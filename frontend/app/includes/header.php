<?php
/**
 * Common Header Include
 * Includes CSS and other head elements
 * Can be used by both full pages and partial pages
 */

// Determine if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Only output the full head if not an AJAX request
if (!$isAjax && !defined('HEAD_INCLUDED')): 
    define('HEAD_INCLUDED', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Tournament Management System'; ?></title>
    <link rel="stylesheet" href="<?php echo getAssetPath('output.css'); ?>">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-900 min-h-screen">
<?php endif; ?>
