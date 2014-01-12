<head>
  <title>Forgot password</title>
</head>
<body bgcolor="#ffffff" text="#000000">

<p>Someone (hopefully you!) requested a password change.  You can change your password by clicking on this link:
<a href="<?=$data['url'] ?>/forgot/<?=$data['verify_hash'] ?>"><?=$data['url'] ?>/forgot/<?=$data['verify_hash'] ?></a>
</p>

<p>If you did NOT request a password change, please ignore this email</p>

<p>
Adventure well,<br/>
<strong>The EoS Team</strong><br/>
<a href="mailto:support@endofseasons.com">support@endofseasons.com</a><br/>
<a href="<?=$data['url'] ?>">End of Seasons</a>
</p>
</body>