<div class="container mt-5 pt-4">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2>Upcoming Movies</h2>
            </div>
        </div>
        <hr class="divider">

        <?php
            include 'admin/db_connect.php';
            $movies = $conn->query("SELECT * FROM movies WHERE date(date_showing) > CURDATE() ORDER BY date_showing ASC");
            while($row=$movies->fetch_assoc()):
        ?>
            <div class="row mt-3 mb-3">
                <div class="col-md-4">
                    <img src="assets/img/<?php echo $row['cover_img']?>" alt="" class="movie-img">
                </div>
                <div class="col-md-8">
                    <div class="card" style="border: none;">
                        <div class="card-body">
                            <div class="container-fluid">
                                <h3><b><?php echo $row['title'] ?></b></h3>
                                <hr>
                                <p class="text-muted"><small><i>Release Date: <?php echo date("M d, Y",strtotime($row['date_showing'])) ?></i></small></p>
                                <p class=""><?php echo $row['description'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
    .movie-img{
        width: 100%;
        max-height: 300px;
        object-fit: cover;
    }
</style>