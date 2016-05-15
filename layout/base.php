<?php getTemplatePart("layout/components/head"); ?>

<body class="js--loading <?php body_class(); ?>">
	<div class="page-wrapper">
		<?php getTemplatePart("layout/components/header"); ?>
		<main id="main">
			<div class="main__content">
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

				getTemplatePart('layout/pages/'.$layout.'');
				?>
				
				<?php getTemplatePart('layout/components/footer'); ?>
			</div>
		</main>
	</div>
	<script type="text/javascript">
		var base_url = '<?php echo ROOT_DIR.'/'; ?>';
		var ajax_url = '<?php echo ROOT_DIR.'/ajax.php'; ?>';
	</script>
	<?php Main::assetQueue('footer'); ?>
	<div class="spinner-wrapper">
		<div class="spinner"></div>
	</div>
</body>
</html>