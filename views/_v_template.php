<!DOCTYPE html>
<html>
<head>
	<title><?php if(isset($title)) echo $title; ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
					
	<!-- Controller Specific JS/CSS -->
	<?php if(isset($client_files_head)) echo $client_files_head; ?>
    <link rel="stylesheet" href="/css/basic-minimal.css" type="text/css"/>
    <link rel="stylesheet" href="/css/jquery-ui.css" type="text/css"/>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" type=text/css">
    <script type="text/javascript" src="/js/jquery-1.10.2.js"></script>
    <script type="text/javascript" src="/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.core.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.widget.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.timer.js"></script>
    <script type="text/javascript" src="/js/jquery.ui.question.js"></script>
    <script type="text/javascript" src="/js/jquery.watermark.js"></script>
    <script type="text/javascript" src="/js/jquery.validate.js"></script>
</head>

<body>
<div style="width:900px;margin-left:auto;margin-right:auto">
<div id='menu' style="width:100%;">

    <div id="spanUsername" style="float:left;width:300px;margin-top: 2px">
        <img src="/images/test_check.png" border="0"/>
        <a href='/'>Our Online Tests</a>

        <?php if($user) { //menu items for logged in users?>
            (<a href='/users/profileedit/<?php echo $user->user_id?>'><?php echo $user->first_name." ".$user->last_name ?></a>)
        <?php }?>
    </div>

    <div id="spanMenu" style="text-align: right;float: right;margin-top: 2px;">
        <?php if($user) { ?>
            <?php if ($user->is_admin) {//menu items for admins?>
                <a href="/tests">Tests |
                <a href="/testtakers">Test Takers |
            <?php }?>
            <a href='/tests/viewhistory'>My Test History</a> |
            <a href='/users/logout'>Logout</a>

        <?php } else { //non-loged-in user's menu?>
            <a href='/users/signup'>Sign up</a> |
            <a href='/users/login'>Log in</a>
        <?php } ?>
    </div>
    <div style="clear:both"></div>
    <hr style="border-top:1px dotted #aaa;">
</div>

<?php if (isset($content->errors)) { ?>
    <?php foreach($content->errors AS $current_error) { ?>
        <div class='alerttext'>
            Error: <?php echo $current_error ?>
        </div>
    <?php } ?>
<?php }?>

    <div id="maincontent">
    <?php if(isset($content)) echo $content; ?>
    </div>

    <footer>
        <hr style="border-top:1px dotted #aaa;">
        © 2013 - Sean Kraft - Contact information: <a href="mailto:sean@seankraft.com">sean@seankraft.com</a>
    </footer>
</div>
<?php if(isset($client_files_body)) echo $client_files_body; ?>
<script>
$( ".button" ).button();
</script>
</body>
</html>