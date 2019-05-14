@extends('layouts.flip')

@section('content')
	<div class="te-container">
		<div class="te-controls" style="display:none">
			<select id="type">
				<option value="te-flip3">Flip 3</option>
			</select>
			<a id="te-next" href="#" class="te-next">next</a>
			<div class="te-shadow"></div>
		</div>
		<div id="te-wrapper" class="te-wrapper">
			<div class="te-images">
				<p>tetete</p>
				<?php
				foreach ($photos as $photo){
					?>
					<img src="<?php echo $photo;?>"/>
					<?php
				}
				?>				
			</div>
			<div class="te-cover">
				<img src="https://tympanus.net/Development/ImageTransitions/images/1.jpg"/>
			</div>
			<div class="te-transition">
				<div class="te-card">
					<div class="te-front"></div>
					<div class="te-back"></div>
				</div>
			</div>
		</div>
	</div>
	<script>
	$( document ).ready(function() {
		$("#te-next").click();		
	});
	</script>
@endsection 