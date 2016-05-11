<?php getTemplatePart("templates/components/head"); ?>

<body class="<?php body_class(); ?>">
	<?php getTemplatePart("templates/components/header"); ?>
	<main id="main">
		<?php 
		$layout = NULL;
		
		if(!isset($Request[PAGE_ID]))
			$layout = 'home';
		else
			switch ($Request[PAGE_ID]) 
		{
			case 'value':
			break;
			
			default: 
			$layout = '404';
			break;
		}

		getTemplatePart('templates/pages/'.$layout.'');
		?>
		<?php getTemplatePart('templates/components/footer'); ?>
	</main>
</body>
</html>