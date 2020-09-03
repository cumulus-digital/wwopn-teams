<?php
/**
 * Shortcode for Team display
 */
namespace WWOPN_Teams;
?>

<?php if ($team && count($team)): ?>

	<section class="<?php echo PREFIX ?>">
	<?php foreach($team as $member): ?>

		<div class="member">
			<figure>
				<img src="<?php echo \esc_url($member->image) ?>" alt="">
			</figure>
			<h3>
				<?php
					echo nl2br(
						strip_tags(
							str_replace(
								array('<br>', '<br/>'),
								"\n",
								$member->post_title
							)
						)
					)
				?>
			</h3>
			<?php if (property_exists($member, 'roles')): ?>
				<ul class="roles">
					<?php foreach($member->roles as $role): ?>
						<li><?php echo \esc_html($role->name) ?></li>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
			<?php if ($show_bio): ?>
				<div class="bio">
					<?php 
						echo str_replace(
							']]>', ']]&gt;', 
							\apply_filters('the_content', $member->post_content)
						);
					?>
				</div>
			<?php endif ?>
		</div>

	<?php endforeach ?>
	</section>

<?php endif ?>