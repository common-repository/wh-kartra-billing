
<div class="bg-white">
  <div class="mx-auto max-w-7xl py-24 px-6 sm:py-32 lg:grid lg:grid-cols-3 lg:gap-x-12 lg:px-8 lg:py-40">
	<div>
	  <h2 class="text-lg font-semibold leading-8 tracking-tight text-indigo-600"><?php esc_html_e( 'Everything you need to play with Kartra!', 'wh-kartra-billing' ); ?></h2>
	  <p class="mt-2 text-4xl font-bold tracking-tight text-gray-900"><?php esc_html_e( 'All-in-one plugin.', 'wh-kartra-billing' ); ?></p>
	  <p class="mt-6 text-base leading-7 text-gray-600">
	  <?php
		esc_html_e(
			'Sync kartra billing actions with WordPress. Just visit kartra interface and use plugins endpoitns against each action in kartra and see the magic.
    		Remember you have to pass certian parameters required by each endpoint ,detials of which are added into the plugins documentation.',
			'wh-kartra-billing'
		);
		?>
		</p>
	</div>
	<div class="mt-20 lg:col-span-2 lg:mt-0">
	  <dl class="grid grid-cols-1 gap-12 sm:grid-flow-col sm:grid-cols-2 sm:grid-rows-4">
		<?php
		global $wh_kartra_actions;
		foreach ( $wh_kartra_actions as $action => $description ) {
			?>
	<div class="relative">
		  <dt>
			<!-- Heroicon name: outline/check -->
			<svg class="absolute mt-1 h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
			</svg>
			<p class="ml-10 text-lg font-semibold leading-8 text-gray-900"><?php echo esc_url_raw( $action ); ?></p>
		  </dt>
		  <dd class="mt-2 ml-10 text-base leading-7 text-gray-600"><?php esc_html_e( $description, 'wh-kartra-billing' ); ?></dd>
		</div>
	<?php } ?>
	  </dl>
	</div>
  </div>
</div>
