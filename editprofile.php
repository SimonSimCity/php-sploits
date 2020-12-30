<?php
require_once 'common.php';
require_once 'dbfuncs.php';

if(!empty($_SESSION['authed']) && $_SESSION['authed'] === true) {
    if(!empty($_SESSION['userid'])) {

        if($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($_POST['uploadBtn']) && $_POST['uploadBtn'] == 'Upload') {
              if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK)
              {
                // get details of the uploaded file
                $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
                $fileName = $_FILES['uploadedFile']['name'];
                $fileSize = $_FILES['uploadedFile']['size'];
                $fileType = $_FILES['uploadedFile']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // sanitize file-name
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                // check if file has one of the following extensions
                $allowedfileExtensions = array('gif', 'png');
                $allowedMimeTypes = array('image/gif', 'image/png');

                if (in_array($fileExtension, $allowedfileExtensions) && in_array(mime_content_type($fileTmpPath), $allowedMimeTypes))
                {
                  // directory in which the uploaded file will be moved
                  $uploadFileDir = './uploaded_files/';
                  $dest_path = $uploadFileDir . $newFileName;

                  if(move_uploaded_file($fileTmpPath, $dest_path))
                  {
                    echo 'File is successfully uploaded.';
                    $updateSQL = "update users set profile_url = '" . $dest_path
                                 . "' where id = " .  $_SESSION['userid'];

                    $updated = insertQuery($updateSQL, true);
                  }
                  else
                  {
                    echo 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                  }
                }
                else
                {
                  echo 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
                  echo ' Allowed mime types: ' . implode(',', $allowedMimeTypes);
                  echo ' Your file is: ' . $fileExtension . ' - ' . mime_content_type($fileTmpPath);
                }
              }
              else
              {
                echo 'There is some error in the file upload. Please check the following error.<br>';
                echo 'Error:' . $_FILES['uploadedFile']['error'];
              }
            }

            if(!empty($_REQUEST['firstname']) && !empty($_REQUEST['surname'])
                && !empty($_REQUEST['email'])) {

                $updateSQL = "update users set firstname = '" . $_REQUEST['firstname']
                            . "', surname = '" . $_REQUEST['surname'] . "', email='" .
                            $_REQUEST['email'] . "' where id = " .  $_SESSION['userid'];

                $updated = insertQuery($updateSQL, true);
                if($updated === false) {
                    echo 'Unable to update your profile.';
                }
                else {
                    echo 'Details updated! Excellent.';
                }
            }
        }
        else {
            $userSQL  = "select email, firstname, surname, profile_url from users where id = " .  $_SESSION['userid'];
            $userList = getSelect($userSQL);

            if(empty($userList) && is_array($userList)) {
                die('Unable to retrieve your settings. Doh!');
            }
            $user = $userList[0];
        ?>
        <form method="POST" enctype="multipart/form-data">
            <p>Edit your settings</p>
            <label for="firstname">Firstname:</label>
            <input name="firstname" id="firstname" value="<?=$user[1]?>" /> <br />
            <label for="surname">Surname:</label>
            <input name="surname" id="surname" value="<?=$user[2]?>" /> <br />
            <label for="email">Email:</label>
            <input name="email" id="email" value="<?=$user[0]?>" /> <br />
            <input type="submit" value="Update profile">
            <br />
            <br />
            <label for="profile_url">Profile:</label>
            <img src="<?=$user[3]?>" /> <br />
            <input type="file" id="file-upload" name="uploadedFile"> <br />
            <input type="submit" name="uploadBtn" value="Upload" />
        </form>
        <?
        }
    }
}
