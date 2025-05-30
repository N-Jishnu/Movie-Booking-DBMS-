<?php 
  include 'admin/db_connect.php';
  $movies = $conn->query("SELECT * FROM movies where '".date('Y-m-d')."' BETWEEN date(date_showing) and date(end_date) order by rand() limit 10");
?>

     
                  <center><h1 class="text-primary" style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 15px; border-bottom: 3px solid #007bff; display: inline-block;">Now Showing</h1></center>

<div id="movie-carousel-field">

  <div class="list-prev list-nav">
    <a href="javascript:void(0)" class="text"><i class="fa fa-angle-left"></i></a>
  </div>
  <div class="list">
    <?php while($row=$movies->fetch_assoc()): ?>
        <div class="movie-item">
          <img class="poster-img" src="assets/img/<?php echo $row['cover_img']  ?>" alt="<?php echo $row['title'] ?>" data-id="<?php echo $row['id'] ?>" data-trailer="<?php echo $row['trailer_yt_link'] ?>" style="cursor: pointer;">
          <div class="mov-det">
            <div class="button-container">
              <button type="button" class="btn btn-primary reserve-btn" data-id="<?php echo $row['id'] ?>">Reserve Seat</button>
            </div>
          </div>
        </div>
    <?php endwhile; ?>
  </div>
   <div class="list-next list-nav">
    <a href="javascript:void(0)" class="text"><i class="fa fa-angle-right"></i></a>
  </div>
</div>

<script>
  
  $('#movie-carousel-field .list-next').click(function(){
    $('#movie-carousel-field .list').animate({
    scrollLeft:$('#movie-carousel-field .list').scrollLeft() + ($('#movie-carousel-field .list').find('img').width() * 3)
   }, 'slow');
  })
  $('#movie-carousel-field .list-prev').click(function(){
    $('#movie-carousel-field .list').animate({
    scrollLeft:$('#movie-carousel-field .list').scrollLeft() - ($('#movie-carousel-field .list').find('img').width() * 3)
   }, 'slow');
  })
  $('.reserve-btn').click(function(){
    location.replace('index.php?page=reserve&id='+$(this).attr('data-id'))
  })
  
  // Handle click on movie poster to open trailer
  $('.poster-img').click(function(){
    var trailerLink = $(this).data('trailer');
    if(trailerLink && trailerLink !== ''){
      window.open(trailerLink, '_blank');
    }
  });
  
  // Watch trailer functionality is now only through poster click
</script>