<?php
class VideoProcessor {

    private $con;
    private $sizeLimit = 500000000;
    private $allowedTypes = array("mp4", "flv", "webm", "mkv", "vob", "ogv", "ogg", "avi", "wmv", "mov", "mpeg", "mpg");
    
    //private $ffmpegPath = "ffmpeg/mac/regular-xampp/ffmpeg"; // *** MAC (USING REGULAR XAMPP) ***
    // private $ffmpegPath = "ffmpeg/mac/xampp-VM/ffmpeg"; // *** MAC (USING XAMPP VM) ***
    // private $ffmpegPath = "ffmpeg/linux/ffmpeg"; // *** LINUX ***
    private $ffmpegPath; //  *** WINDOWS ***
    private $ffprobePath;

    public function __construct($con) {
        $this->con = $con;
        $this->ffmpegPath = realpath("ffmpeg/windows/ffmpeg.exe");
        $this->ffprobePath = realpath("ffmpeg/windows/ffprobe.exe");
    }

    public function upload($videoUploadData) {

        $targetDir = "uploads/videos/";
        $videoData = $videoUploadData->videoDataArray;

        $tempFilePath = $targetDir . uniqid() . basename($videoData["name"]);
        $tempFilePath = str_replace(" ", "_", $tempFilePath);

        $isValidData = $this->processData($videoData, $tempFilePath);

        if(!$isValidData) {
            return false;
        }

        if(move_uploaded_file($videoData["tmp_name"], $tempFilePath)) {
            $finalFilePath = $targetDir . uniqid() . ".mp4";

            if(!$this->insertVideoData($videoUploadData, $finalFilePath)) {
                echo "Insert query failed\n";
                return false;
            }

            if(!$this->convertVideoToMp4($tempFilePath, $finalFilePath)) {
                echo "Upload failed\n";
                return false;
            }

            if(!$this->deleteFile($tempFilePath)) {
                echo "Upload failed\n";
                return false;
            }

            if(!$this->generateThumbnails($finalFilePath)) {
                echo "Upload failed - could not generate thumbnails\n";
                return false;
            }


        }
    }

    private function processData($videoData, $filePath) {
        $videoType = pathInfo($filePath, PATHINFO_EXTENSION);
        
        if(!$this->isValidSize($videoData)) {
            echo "File too large. Can't be more than " . $this->sizeLimit . " bytes";
            return false;
        }
        else if(!$this->isValidType($videoType)) {
            echo "Invalid file type";
            return false;
        }
        else if($this->hasError($videoData)) {
            echo "Error code: " . $videoData["error"];
            return false;
        }

        return true;
    }

    private function isValidSize($data) {
        return $data["size"] <= $this->sizeLimit;
    }

    private function isValidType($type) {
        $lowercased = strtolower($type);
        return in_array($lowercased, $this->allowedTypes);
    }
    
    private function hasError($data) {
        return $data["error"] != 0;
    }

    private function insertVideoData($uploadData, $filePath) {
        $query = $this->con->prepare("INSERT INTO videos(title, uploadedBy, description, privacy, category, filePath)
                                        VALUES(:title, :uploadedBy, :description, :privacy, :category, :filePath)");

        $query->bindParam(":title", $uploadData->title);
        $query->bindParam(":uploadedBy", $uploadData->uploadedBy);
        $query->bindParam(":description", $uploadData->description);
        $query->bindParam(":privacy", $uploadData->privacy);
        $query->bindParam(":category", $uploadData->category);
        $query->bindParam(":filePath", $filePath);

        return $query->execute();
    }

    public function convertVideoToMp4($tempFilePath, $finalFilePath) {
        $cmd = "$this->ffmpegPath -i $tempFilePath $finalFilePath 2>&1";

        $outputLog = array();
        exec($cmd, $outputLog, $returnCode);
        
        if($returnCode != 0) {
            //Command failed
            foreach($outputLog as $line) {
                echo $line . "<br>";
            }
            return false;
        }

        return true;
    }

    private function deleteFile($filePath) {
        if(!unlink($filePath)) {
            echo "Could not delete file\n";
            return false;
        }

        return true;
    }

    public function generateThumbnails($filePath) {
        $thumbnailSize = "210x118";  // YOUTUBE'S THUMBNAIL SIZE
        $numThumbnails = 3;
        $pathToThumbnail = "uploads/videos/thumbnails";

        $duration = $this -> getVideoDuration($filePath);

        $videoId = $this->con->lastInsertId();
        $this->updateDuration($duration, $videoId);

        // echo "duration: $duration";
    }

    private function getVideoDuration($filePath) {
        return shell_exec("$this->ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $filePath");
    }

    private function updateDuration($duration, $videoId) {
        $hours = floor($duration/3600);
        $mins = floor(($duration - ($hours * 3600)) / 60);
        $secs = floor($duration % 60);

        /*
        One way to do it is with multi lines
        if ($hours < 1) {
            $hours = "";
        }
        else {
            $hours = $hours . ":";
        }
        */

        $hours = ($hours < 1) ? "" : $hours . ":";
        $mins = ($mins < 10) ? "0" . $mins . ":" : $mins . ":";
        $secs = ($secs < 10) ? "0" . $secs : $secs;

        $duration = $hours.$mins.$secs;

        $query = $this->con->prepare("UPDATE videos SET duration=:duration WHERE id=:videoId");
        // Bind duration and videoId placeholders to the right values stored in this variables
        $query->bindParam(":duration", $duration);
        $query->bindParam(":videoId", $videoId);
        $query->execute();
    }
}
?>