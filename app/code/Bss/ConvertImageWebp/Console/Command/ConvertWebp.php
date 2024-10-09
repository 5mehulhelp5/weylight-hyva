<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ConvertImageWebp
 * @author    Extension Team
 * @copyright Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use WebPConvert\WebPConvert;

class ConvertWebp extends Command
{
    /**
     * @var \Bss\ConvertImageWebp\Model\Convert
     */
    protected $convert;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * ConvertWebp constructor.
     *
     * @param \Bss\ConvertImageWebp\Model\Convert $convert
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string|null $name
     */
    public function __construct(
        \Bss\ConvertImageWebp\Model\Convert $convert,
        \Magento\Framework\Filesystem     $filesystem,
        string                            $name = null
    ) {
        $this->convert = $convert;
        $this->filesystem = $filesystem;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('bss_c_i_w:convert')
            ->setDescription('Convert image to webp')
            ->addArgument('path', null, 'Path Folder')
            ->addArgument('quality', null, 'Update Quality');
    }

    /**
     * Convert Image from special path folder or file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path') ? (string)$input->getArgument('path') : 'pub/media';
        $quality = $input->getArgument('quality');

        try {
            if ($quality !== null) {
                if ($quality != (int)$quality || $quality < 1 || $quality > 100) {
                    $output->writeln(__('Please enter an integer in quality, greater than 1 and less than 100!'));
                    return 1;
                } else {
                    $quality = (int)$quality;
                }
            }

            $isFolder = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->isDirectory($path);

            if ($isFolder) {
                $progressBar = $this->getProgressBar($output);
                $paths = $this->convert->getAllFiles($path);
                $qualityWebp = $quality ? $quality : $this->convert->getQualityImageWebp();

                $count = 0;
                $progressBar->start(count($paths));
                $startTime = microtime(true);
                foreach ($paths as $path) {
                    $progressBar->setMessage($path, "path");
                    if ($this->convert->isImageFile($path)) {
                        $destinationImageUri = $this->convert->getDestinationImageUriCommand($path);
                        try {
                            if (!$this->convert->checkExistsFile($destinationImageUri)) {
                                WebPConvert::convert($path, $destinationImageUri, $this->convert->getOptions($qualityWebp));
                                $count++;
                            } elseif ($quality) {
                                WebPConvert::convert($path, $destinationImageUri, $this->convert->getOptions($quality));
                                $count++;
                            }
                        } catch (\Exception $e) {
                            $output->writeln("");
                            $output->writeln("<error>" . $e->getMessage() . " at: </error> <comment>" . $path . "</comment>");
                            $output->writeln("");
                            $output->writeln("destination Image Uri: " . $destinationImageUri);
                            $this->convert->writeLog($e);
                        }
                    }

                    $progressBar->advance();
                }
                $progressBar->finish();
                $output->writeln("");
                $output->writeln(
                    sprintf(
                        "<info>Convert successfully: %s image(s) in %s second!</info>",
                        $count,
                        round(microtime(true) - $startTime, 2)
                    )
                );
                return 1;
            } else {
                $this->convert->convertImageCommand($path, $quality);
            }
        } catch (\Exception $exception) {
            $this->convert->writeLog($exception->getMessage());
        }
        return 1;
    }

    /**
     * Get progress bar
     *
     * @param OutputInterface $output
     * @return ProgressBar
     */
    protected function getProgressBar(OutputInterface $output): ProgressBar
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setRedrawFrequency(1);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%path%</info>"
        );
        return $progressBar;
    }
}
