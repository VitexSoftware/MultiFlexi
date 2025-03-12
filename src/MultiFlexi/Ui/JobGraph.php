<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

class JobGraph
{
    private $width;
    private $height;
    private $todaysJobs;
    private $image;
    private $successCount = 0;
    private $failureCount = 0;
    private $noExecutableCount = 0;
    private $exceptionCount = 0;
    private $waitingCount = 0;

    public function __construct(int $width, int $height, array $todaysJobs)
    {
        $this->width = $width;
        $this->height = $height;
        $this->todaysJobs = $todaysJobs;
    }

    public function calcultateStats(): void
    {
        foreach ($this->todaysJobs as $job) {
            if ($job['exitcode'] === 0) {
                ++$this->successCount;
            } elseif ($job['exitcode'] === 127) {
                ++$this->noExecutableCount;
            } elseif ($job['exitcode'] === 255) {
                ++$this->exceptionCount;
            } elseif (null === $job['exitcode']) {
                ++$this->waitingCount;
            } else {
                ++$this->failureCount;
            }
        }
    }

    public function generateImage(): void
    {
        $this->image = imagecreatetruecolor($this->width, $this->height);

        // Allocate colors
        $white = imagecolorallocate($this->image, 255, 255, 255);
        imagefill($this->image, 0, 0, $white);

        $centerX = $this->width / 2;
        $centerY = $this->height / 2;
        $x = $centerX;
        $y = $centerY;
        $dx = 1;
        $dy = 0;
        $segment_length = 1;
        $segment_passed = 0;
        $totalJobs = \count($this->todaysJobs);

        foreach ($this->todaysJobs as $index => $job) {
            // Calculate color intensity based on the age of the data
            $intensity = 128 + (int) (127 * ($index / $totalJobs)); // Make older data pixels darker
            $green = imagecolorallocate($this->image, 0, $intensity, 0);
            $red = imagecolorallocate($this->image, $intensity, 0, 0);
            $yellow = imagecolorallocate($this->image, $intensity, $intensity, 0);
            $black = imagecolorallocate($this->image, 0, 0, 0);
            $blue = imagecolorallocate($this->image, 0, 0, $intensity);

            if ($job['exitcode'] === 0) {
                $color = $green;
                ++$this->successCount;
            } elseif ($job['exitcode'] === 127) {
                $color = $yellow;
                ++$this->noExecutableCount;
            } elseif ($job['exitcode'] === 255) {
                $color = $black;
                ++$this->exceptionCount;
            } elseif (null === $job['exitcode']) {
                $color = $blue;
                ++$this->waitingCount;
            } else {
                $color = $red;
                ++$this->failureCount;
            }

            imagesetpixel($this->image, $x, $y, $color);

            $x += $dx;
            $y += $dy;
            ++$segment_passed;

            if ($segment_passed === $segment_length) {
                $segment_passed = 0;

                // Change direction
                $temp = $dx;
                $dx = -$dy;
                $dy = $temp;

                // Increase segment length after two segments
                if ($dy === 0) {
                    ++$segment_length;
                }
            }

            // Stop if we reach the edge of the image
            if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
                break;
            }
        }
    }

    public function getBase64Image(): string
    {
        // Encode the image data as base64
        return base64_encode($this->getImage());
    }

    public function getImage(): string
    {
        // Capture the image output
        ob_start();
        imagepng($this->image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($this->image);

        return $imageData;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getNoExecutableCount(): int
    {
        return $this->noExecutableCount;
    }

    public function getExceptionCount(): int
    {
        return $this->exceptionCount;
    }

    public function getWaitingCount(): int
    {
        return $this->waitingCount;
    }

    public function getTotalJobs(): int
    {
        return \count($this->todaysJobs);
    }

    public function getImageTag($companyId)
    {
        return new \Ease\Html\ImgTag(
            'jobgraph.php?width='.$this->width.'&height='.$this->height.'&company_id='.$companyId,
            _('Job Success/Failure Graph'),
            ['width' => $this->width, 'height' => $this->height],
        );
    }
}
