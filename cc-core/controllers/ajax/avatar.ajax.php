<?php

Plugin::triggerEvent('upload.start');
$this->view->options->disableView = true;

// Verify if user is logged in
$userService = new UserService();
$loggedInUser = $userService->loginCheck();
Functions::RedirectIf($loggedInUser, HOST . '/login/');

// Retrieve video information
if (!isset($_POST['timestamp'])) App::Throw404();

// Validate upload key
$upload_key = md5(md5($_POST['timestamp']) . SECRET_KEY);
if (!isset ($_SESSION['upload_key']) || $_SESSION['upload_key'] != $upload_key) App::Throw404();

try {
    // Verify upload was made
    if (empty ($_FILES) || !isset ($_FILES['upload']['name'])) {
        throw new Exception (Language::GetText('error_upload_empty')) ;
    }
    
    // Check for upload errors
    if ($_FILES['upload']['error'] != 0) {
        App::Alert ('Error During Avatar Upload', 'There was an HTTP FILE POST error (Error code #' . $_FILES['upload']['error'] . ').');
        throw new Exception (Language::GetText('error_upload_system', array ('host' => HOST)));
    }

    // Validate filesize
    if ($_FILES['upload']['size'] > 1024*30 || filesize ($_FILES['upload']['tmp_name']) > 1024*30) {
        throw new Exception (Language::GetText('error_upload_filesize'));
    }

    // Validate avatar extension
    $extension = Functions::GetExtension ($_FILES['upload']['name']);
    if (!in_array ($extension, array('gif','png','jpg','jpeg'))) {
        throw new Exception (Language::GetText('error_upload_extension'));
    }

    // Validate image data
    $handle = fopen ($_FILES['upload']['tmp_name'],'r');
    $image_data = fread ($handle, filesize ($_FILES['upload']['tmp_name']));
    if (!@imagecreatefromstring ($image_data)) throw new Exception (Language::GetText('error_upload_extension'));

    // Change permissions on avatar & delete previous IF/APP
    try {
        $avatar_path = UPLOAD_PATH . '/avatars';
        $save_as = Avatar::CreateFilename ($extension);
        Avatar::SaveAvatar ($_FILES['upload']['tmp_name'], $extension, $save_as);

        // Check for existing avatar
        if (!empty ($loggedInUser->avatar)) Avatar::Delete ($loggedInUser->avatar);

        Filesystem::setPermissions("$avatar_path/$save_as", 0644);
    } catch (Exception $e) {
        App::Alert ('Error During Avatar Upload', $e->getMessage());
        throw new Exception (Language::GetText('error_upload_system', array ('host' => HOST)));
    }

    // Update User
    $userMapper = new UserMapper();
    $loggedInUser->avatar = $save_as;
    $userMapper->save($loggedInUser);
    Plugin::Trigger ('update_profile.update_avatar');

    // Output success message
    exit (json_encode (array ('result' => true, 'message' => (string) Language::GetText('success_avatar_updated'), 'other' => $userService->getAvatarUrl($loggedInUser))));
} catch (Exception $e) {
    exit (json_encode (array ('result' => false, 'message' => $e->getMessage())));
}