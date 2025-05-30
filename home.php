<!-- Masthead-->
        <header class="masthead">
            <div class="container h-100">
                <?php include 'movie_carousel.php' ?>
            </div>
        </header>

<!-- Featured Movie Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading text-uppercase mb-4">Featured Movie</h2>
            </div>
        </div>
        <?php 
            include 'admin/db_connect.php';
            $featured = $conn->query("SELECT * FROM movies where '" . date('Y-m-d') . "' BETWEEN date(date_showing) and date(end_date) order by rand() limit 1");
            $frow = $featured->fetch_assoc();
            if($featured->num_rows > 0):
                $duration = explode('.', $frow['duration']);
                $hr = sprintf("%'.02d\n",$duration[0]);
                $min = isset($duration[1]) ? (60 * ('.'.$duration[1])) : '0';
                $min = sprintf("%'.02d\n",$min);
                // $min = $min > 0 ? $min : '00';
                $duration = $hr.' : '.$min;
        ?>
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="assets/img/<?php echo $frow['cover_img'] ?>" class="img-fluid rounded shadow featured-poster" alt="<?php echo $frow['title'] ?>" data-trailer="<?php echo $frow['trailer_yt_link'] ?>" style="cursor: pointer;">
            </div>
            <div class="col-md-6">
                <h3 class="mb-3"><?php echo $frow['title'] ?></h3>
                <p class="lead"><?php echo $frow['description'] ?></p>
                <p><i class="fa fa-clock-o"></i> Duration: <?php echo $duration ?></p>
                <button class="btn btn-primary btn-lg" onclick="location.replace('index.php?page=reserve&id='+<?php echo $frow['id'] ?>)">Reserve Now</button>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-12 text-center">
                <p>No featured movie available at the moment.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
  // Make featured movie poster clickable to open trailer
  $('.featured-poster').click(function(){
    var trailerLink = $(this).data('trailer');
    if(trailerLink && trailerLink !== ''){
      window.open(trailerLink, '_blank');
    }
  });
</script>

<!-- Upcoming Movies Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="section-heading text-uppercase mb-4">Coming Soon</h2>
            </div>
        </div>
        <div class="row">
            <?php 
                $upcoming = $conn->query("SELECT * FROM movies where date(date_showing) > '" . date('Y-m-d') . "' order by date_showing asc limit 3");
                if($upcoming->num_rows > 0):
                    while($urow = $upcoming->fetch_assoc()):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <img class="card-img-top upcoming-poster" src="assets/img/<?php echo $urow['cover_img'] ?>" alt="<?php echo $urow['title'] ?>" data-trailer="<?php echo $urow['trailer_yt_link'] ?>" style="cursor: pointer;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $urow['title'] ?></h5>
                        <p class="card-text small"><?php echo substr($urow['description'], 0, 100) ?>...</p>
                        <p class="card-text"><small class="text-muted">Coming on: <?php echo date("M d, Y", strtotime($urow['date_showing'])) ?></small></p>
                    </div>
                </div>
            </div>
            <?php 
                    endwhile;
                else:
            ?>
            <div class="col-12 text-center">
                <p>No upcoming movies scheduled at the moment.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
  // Make featured movie poster clickable to open trailer
  $('.featured-poster').click(function(){
    var trailerLink = $(this).data('trailer');
    if(trailerLink && trailerLink !== ''){
      window.open(trailerLink, '_blank');
    }
  });
</script>

<!-- Newsletter Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-heading mb-3">Subscribe to Our Newsletter</h2>
                <p class="mb-4">Get the latest updates on new movies, special screenings, and exclusive offers!</p>
                <form class="form-inline justify-content-center">
                    <div class="input-group mb-2 mr-sm-2 mb-sm-0 col-sm-8 p-0">
                        <input type="email" class="form-control" id="newsletter-email" placeholder="Enter your email">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-light">Subscribe</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
  // Make featured movie poster clickable to open trailer
  $('.featured-poster').click(function(){
    var trailerLink = $(this).data('trailer');
    if(trailerLink && trailerLink !== ''){
      window.open(trailerLink, '_blank');
    }
  });
</script>
