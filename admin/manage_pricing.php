<?php 
include 'db_connect.php';
$movies = $conn->query("SELECT * FROM movies where '" . date('Y-m-d') . "' between date(date_showing) and date(end_date) order by title asc");
$theaters = $conn->query("SELECT * FROM theater where status = 'active' order by name asc");

// Get movie_id and theater_id from URL if provided
$movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : '';
$theater_id = isset($_GET['theater_id']) ? $_GET['theater_id'] : '';

// If both movie_id and theater_id are provided, fetch pricing data
$pricing_data = array();
if(!empty($movie_id) && !empty($theater_id)) {
    $pricing = $conn->query("SELECT * FROM movie_pricing WHERE movie_id = $movie_id AND theater_id = $theater_id");
    while($row = $pricing->fetch_assoc()) {
        $pricing_data[$row['seat_group']] = $row['price'];
    }
}

// Get seat groups for selected theater
$seat_groups = array();
if(!empty($theater_id)) {
    $seats = $conn->query("SELECT * FROM theater_settings WHERE theater_id = $theater_id AND status = 'active'");
    while($row = $seats->fetch_assoc()) {
        $seat_groups[] = $row;
    }
}
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <form id="manage-pricing">
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="movie_id" class="control-label">Select Movie</label>
                    <select name="movie_id" id="movie_id" class="custom-select browser-default" required>
                        <option value=""></option>
                        <?php while($row = $movies->fetch_assoc()): ?>
                            <option value="<?php echo $row['id'] ?>" <?php echo $movie_id == $row['id'] ? 'selected' : '' ?>>
                                <?php echo $row['title'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="theater_id" class="control-label">Select Theater</label>
                    <select name="theater_id" id="theater_id" class="custom-select browser-default" required>
                        <option value=""></option>
                        <?php while($row = $theaters->fetch_assoc()): ?>
                            <option value="<?php echo $row['id'] ?>" <?php echo $theater_id == $row['id'] ? 'selected' : '' ?>>
                                <?php echo $row['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <?php if(!empty($seat_groups)): ?>
                <hr>
                <div class="form-group row">
                    <div class="col-md-12">
                        <h4>Set Pricing for Seat Groups</h4>
                    </div>
                </div>
                <?php foreach($seat_groups as $seat): ?>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label class="control-label"><?php echo $seat['seat_group'] ?> (<?php echo $seat['seat_count'] ?> seats)</label>
                            <input type="hidden" name="seat_group[]" value="<?php echo $seat['seat_group'] ?>">
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="price[]" class="form-control" placeholder="Price" step="0.01" min="0" value="<?php echo isset($pricing_data[$seat['seat_group']]) ? $pricing_data[$seat['seat_group']] : '' ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="form-group row">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary btn-sm" type="submit">Save Pricing</button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    $(document).ready(function(){
        // When movie or theater selection changes, reload the page with the new selections
        $('#movie_id, #theater_id').change(function(){
            var movie_id = $('#movie_id').val();
            var theater_id = $('#theater_id').val();
            if(movie_id != '' && theater_id != ''){
                location.href = 'index.php?page=manage_pricing&movie_id=' + movie_id + '&theater_id=' + theater_id;
            }
        });

        // Handle form submission
        $('#manage-pricing').submit(function(e){
            e.preventDefault();
            start_load();
            $.ajax({
                url: 'ajax.php?action=save_pricing',
                method: 'POST',
                data: $(this).serialize(),
                success: function(resp){
                    if(resp == 1){
                        alert_toast('Pricing data successfully saved', 'success');
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                    }
                }
            });
        });
    });
</script>