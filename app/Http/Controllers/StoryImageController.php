<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Typography\FontFactory;

class StoryImageController extends Controller
{
    public function generateShareImage($slug)
    {
        $story = Story::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Check if image already exists
        $filename = "share-images/{$story->slug}.png";

        if (Storage::disk('public')->exists($filename)) {
            return response()->file(storage_path("app/public/{$filename}"));
        }

        // Create image
        $image = $this->createShareImage($story);

        // Save to storage
        Storage::disk('public')->makeDirectory('share-images');
        Storage::disk('public')->put($filename, $image->toPng());

        return response()->file(storage_path("app/public/{$filename}"));
    }

    private function createShareImage(Story $story)
    {
        // Image dimensions (optimal for social sharing)
        $width = 1200;
        $height = 630;
        $padding = 60;

        // Create canvas
        $image = Image::create($width, $height);

        // Background - clean white
        $image->fill('#ffffff');

        // Add subtle gradient overlay for depth
        $image->drawRectangle(0, 0, function ($rectangle) use ($width, $height) {
            $rectangle->size($width, $height);
            $rectangle->background('rgba(249, 250, 251, 0.5)');
        });

        // Add accent bar at top
        $image->drawRectangle(0, 0, function ($rectangle) use ($width) {
            $rectangle->size($width, 8);
            $rectangle->background('#000000');
        });

        // Draw content area
        $contentX = $padding;
        $contentWidth = $width - ($padding * 2);
        $currentY = $padding + 20;

        // Top section: Username, timestamp, category
        $currentY = $this->drawTopSection($image, $story, $contentX, $currentY, $contentWidth);

        // Title
        $currentY += 40;
        $currentY = $this->drawTitle($image, $story->title, $contentX, $currentY, $contentWidth);

        // Body excerpt
        $currentY += 30;
        $currentY = $this->drawBodyExcerpt($image, $story->body, $contentX, $currentY, $contentWidth);

        // Bottom stats
        $this->drawBottomStats($image, $story, $padding, $height - $padding - 40);

        // Add "Bluntly" branding at bottom right
        $image->text('Bluntly', $width - $padding, $height - 30, function (FontFactory $font) {
            $font->size(20);
            $font->color('#9ca3af');
            $font->align('right');
        });

        return $image;
    }

    private function drawTopSection($image, $story, $x, $y, $width)
    {
        // Username with better styling
        $image->text("@{$story->alias}", $x, $y, function (FontFactory $font) {
            $font->size(26);
            $font->color('#111827');
            $font->align('left');
        });

        // Timestamp
        $timeAgo = $story->created_at->diffForHumans();
        $image->text($timeAgo, $x + 220, $y, function (FontFactory $font) {
            $font->size(24);
            $font->color('#9ca3af');
            $font->align('left');
        });

        // Category badge with modern styling
        if ($story->category) {
            $categoryX = $x + 450;
            $categoryText = strtoupper($story->category);

            // Badge background - more prominent
            $image->drawRectangle($categoryX, $y - 22, function ($rectangle) use ($categoryText) {
                $width = strlen($categoryText) * 16 + 40;
                $rectangle->size($width, 42);
                $rectangle->background('#000000');
            });

            // Badge text
            $image->text($categoryText, $categoryX + 20, $y, function (FontFactory $font) {
                $font->size(20);
                $font->color('#ffffff');
                $font->align('left');
            });
        }

        return $y + 35;
    }

    private function drawTitle($image, $title, $x, $y, $maxWidth)
    {
        // Word wrap title
        $words = explode(' ', $title);
        $lines = [];
        $currentLine = '';
        $maxCharsPerLine = 50;

        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            if (strlen($testLine) > $maxCharsPerLine && $currentLine !== '') {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== '') {
            $lines[] = trim($currentLine);
        }

        // Limit to 2 lines for better readability
        $lines = array_slice($lines, 0, 2);
        if (count($lines) == 2 && strlen($title) > 100) {
            $lines[1] = rtrim($lines[1], '.') . '...';
        }

        // Draw each line with bold, prominent styling
        $lineHeight = 60;
        foreach ($lines as $line) {
            $image->text($line, $x, $y, function (FontFactory $font) {
                $font->size(52);
                $font->color('#111827');
                $font->align('left');
            });
            $y += $lineHeight;
        }

        return $y;
    }

    private function drawBodyExcerpt($image, $body, $x, $y, $maxWidth)
    {
        // Clean and limit body text
        $bodyText = strip_tags($body);
        $bodyText = substr($bodyText, 0, 250);

        // Word wrap
        $words = explode(' ', $bodyText);
        $lines = [];
        $currentLine = '';
        $maxCharsPerLine = 75;

        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            if (strlen($testLine) > $maxCharsPerLine && $currentLine !== '') {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== '') {
            $lines[] = trim($currentLine);
        }

        // Limit to 3 lines
        $lines = array_slice($lines, 0, 3);

        // Draw each line with improved readability
        $lineHeight = 38;
        foreach ($lines as $index => $line) {
            if ($index == 2 && strlen($bodyText) > 200) {
                $line = rtrim($line, '.') . '...';
            }

            $image->text($line, $x, $y, function (FontFactory $font) {
                $font->size(28);
                $font->color('#6b7280');
                $font->align('left');
            });
            $y += $lineHeight;
        }

        return $y;
    }

    private function drawBottomStats($image, $story, $x, $y)
    {
        $spacing = 160;
        $currentX = $x;

        // Upvotes with better styling
        $image->text("↑", $currentX, $y, function (FontFactory $font) {
            $font->size(36);
            $font->color('#22c55e');
            $font->align('left');
        });
        $image->text($story->upvotes, $currentX + 35, $y, function (FontFactory $font) {
            $font->size(30);
            $font->color('#111827');
            $font->align('left');
        });

        // Downvotes
        $currentX += $spacing;
        $image->text("↓", $currentX, $y, function (FontFactory $font) {
            $font->size(36);
            $font->color('#ef4444');
            $font->align('left');
        });
        $image->text($story->downvotes, $currentX + 35, $y, function (FontFactory $font) {
            $font->size(30);
            $font->color('#111827');
            $font->align('left');
        });

        // Comments
        $currentX += $spacing;
        $image->text("💬", $currentX, $y, function (FontFactory $font) {
            $font->size(32);
            $font->align('left');
        });
        $image->text($story->comments->count(), $currentX + 45, $y, function (FontFactory $font) {
            $font->size(30);
            $font->color('#111827');
            $font->align('left');
        });

        // Views
        $currentX += $spacing;
        $image->text("👁", $currentX, $y, function (FontFactory $font) {
            $font->size(32);
            $font->align('left');
        });
        $image->text(number_format($story->views), $currentX + 45, $y, function (FontFactory $font) {
            $font->size(30);
            $font->color('#111827');
            $font->align('left');
        });
    }

    public function clearCache($slug)
    {
        $filename = "share-images/{$slug}.png";

        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
