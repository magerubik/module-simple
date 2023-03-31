<?php
namespace Magerubik\Simple\Model;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
class ImageProcessor
{
    const SIMPLE_MEDIA_PATH = 'simple/message';
    const SIMPLE_MEDIA_TMP_PATH = 'simple/message/tmp';
    private $imageUploader;
    private $imageFactory;
    private $storeManager;
    private $mediaDirectory;
    private $filesystem;
    private $ioFile;
    private $coreFileStorageDatabase;
    private $logger;
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->ioFile = $ioFile;
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->logger = $logger;
    }
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }
    public function getThumbnailUrl($imageName)
    {
        $pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        if ($pubDirectory->isExist($imageName)) {
            $result = $this->storeManager->getStore()->getBaseUrl() . trim($imageName, '/');
        } else {
            $result = $this->getCategoryIconMedia(self::SIMPLE_MEDIA_PATH) . '/' . $imageName;
        }
        return $result;
    }
    private function getImageRelativePath($iconName)
    {
        return self::SIMPLE_MEDIA_PATH . DIRECTORY_SEPARATOR . $iconName;
    }
    private function getCategoryIconMedia($mediaPath)
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $mediaPath;
    }
    public function processCategoryIcon($iconName)
    {
        $this->imageUploader->moveFileFromTmp($iconName, true);
        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($iconName));
        try {
            $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
            $imageProcessor->keepAspectRatio(true);
            $imageProcessor->keepFrame(true);
            $imageProcessor->keepTransparency(true);
            $imageProcessor->backgroundColor([255, 255, 255]);
            $imageProcessor->save();
        } catch (\Exception $e) {
            null;
        }
    }
    public function moveFile(array $images): ?string
    {
        $filePath = null;
        if (count($images) > 0) {
            foreach ($images as $image) {
                if (array_key_exists('file', $image)) {
                    $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                    if ($mediaDirectory->isExist(self::SIMPLE_MEDIA_TMP_PATH . '/' . $image['file'])) {
                        $filePath = $this->moveFileFromTmp($image['file']);
                        break;
                    }
                } elseif (isset($image['type'])) {
                    $filePath = $image['url'] ?? '';
                }
            }
        }
        return $filePath;
    }
    public function deleteImage($iconName)
    {
        $this->getMediaDirectory()->delete($this->getImageRelativePath($iconName));
    }
    public function copy($imageName)
    {
        $basePath = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        $imageName = explode('.', $imageName);
        $imageName[0] .= '-' . random_int(1, 1000);
        $imageName = implode('.', $imageName);
        $newPath = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        try {
            $this->ioFile->cp(
                $basePath,
                $newPath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
        return $imageName;
    }
    public function moveFileFromTmp($imageName, $returnRelativePath = false): string
    {
        $baseTmpPath = $this->imageUploader->getBaseTmpPath();
        $basePath = $this->imageUploader->getBasePath();
        $baseImagePath = $this->imageUploader->getFilePath(
            $basePath,
            Uploader::getNewFileName(
                $this->getMediaDirectory()->getAbsolutePath($this->imageUploader->getFilePath($basePath, $imageName))
            )
        );
        $baseTmpImagePath = $this->imageUploader->getFilePath($baseTmpPath, $imageName);
        try {
            $this->coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);
            $this->getMediaDirectory()->renameFile($baseTmpImagePath, $baseImagePath);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).'),
                $e
            );
        }
        return $returnRelativePath ? $baseImagePath : $imageName;
    }
}