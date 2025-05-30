<?php 
  include 'admin/db_connect.php';
  $movies = $conn->query("SELECT * FROM movies where '".date('Y-m-d')."' BETWEEN date(date_showing) and date(end_date) order by rand()");
?>

<header class="masthead">
	<div class="container-fluid">	
		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">
					<center><h1 class="text-primary movie-section-title" style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 15px; border-bottom: 3px solid #007bff; display: inline-block;">Now Showing</h1></center>
				</div>
			</div>
			<div class="movie-scroll-container mt-3" id="movies">	
			<?php while($row=$movies->fetch_assoc()): 
				$duration = explode('.', $row['duration']);
				$hr = sprintf("%'.02d\n",$duration[0]);
				$min = isset($duration[1]) ? (60 * ('.'.$duration[1])) : '0';
				$min = sprintf("%'.02d\n",$min);
				$duration = $hr.' : '.$min;
			?>
			<div class="movie-item">
				<div class="card movie-card">
					<div class="movie-image-container">
						<img class="card-img-top" src="assets/img/<?php echo $row['cover_img'] ?>" alt="<?php echo $row['title'] ?>">
					</div>
					<div class="card-body">
						<h5 class="card-title"><?php echo $row['title'] ?></h5>
						<div class="card-subtitle mb-2 text-muted"><i class="fa fa-clock-o"></i> Duration: <?php echo $duration ?></div>
						<div class="container-fluid p-0 mt-auto text-center">
							<button type="button" class="btn btn-primary btn-reserve" data-id="<?php echo $row['id'] ?>">Reserve Seat</button>
						</div>
					</div>
				</div>
			</div>
    <?php endwhile; ?>
		</div>
	</div>	
</header>

<style>
.movie-section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 10px;
    border-bottom: 2px solid #007bff;
    display: inline-block;
}

.movie-scroll-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    padding: 20px 0;
    overflow-x: hidden;
    overflow-y: auto;
    max-height: none;
    width: 100%;
}

.movie-scroll-container::-webkit-scrollbar {
    height: 8px;
}

.movie-scroll-container::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.movie-scroll-container::-webkit-scrollbar-thumb {
    background-color: #007bff;
    border-radius: 10px;
}

.movie-item {
    flex: 0 0 auto;
    width: 280px;
    margin-right: 5px;
}
.movie-image-container {
    height: 350px;
    overflow: hidden;
}

.card-img-top {
    height: 100%;
    width: 100%;
    object-fit: cover;
    max-width: 100%;
}

.card-body {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    height: auto;
}

.card-title {
    font-weight: 600;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-subtitle {
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.btn-reserve {
    width: 100%;
    border-radius: 4px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.5rem 1rem;
}
@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 20px;
    }
    
    .movie-image-container {
        height: 250px;
    }
}
</style>

<script>
	$('.btn-reserve').click(function(){
		location.replace('index.php?page=reserve&id='+$(this).attr('data-id'))
	})
</script>