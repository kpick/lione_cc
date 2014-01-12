<head>
  <title>Welcome to EndOfSeasons!</title>
</head>
<body bgcolor="#ffffff" text="#000000">
<h3>Hail adventurer!</h3>
<p>Welcome to the End of Seasons community.</p>

<h3>Login info</h3>
<p>
You can login at <a href="<?=$data['url'] ?>/players/login"><?=$data['url'] ?>/players/login</a> with your email address:  
<?=$data['login'] ?>.  If you want to set a username, create a character, or view the forums, you need to activate your account 
(see below)
</p>

<h3>Account Activation</h3>
<p>Before you can create characters, edit your profile, or view the forums, you must activate your
account at the following address: 
<a href="<?=$data['url'] ?>/confirm/<?=$data['verify_hash'] ?>"><?=$data['url'] ?>/confirm/<?=$data['verify_hash'] ?></a>
</p>

<h3>Next Steps:</h3>
<ul>

<li>
Read the core rules and setting info at 
</li>
<li>
Edit your profile at
</li>
<li>
View the forums at
</li>
</ul>

<p>
Adventure well,<br/>
<strong>The EoS Team</strong><br/>
<a href="mailto:support@endofseasons.com">support@endofseasons.com</a><br/>
<a href="<?=$data['url'] ?>">End of Seasons</a>
</p>
</body>