<? 	$website_id = 6; // REMOVE AFTER TESTING
	if ($_POST['page_path']) { 
		
		$p->title="SEO - ".$_POST['page_path'];
		$p->template('skybox','top');
		if ($_POST['website_ide']) 
		$website_id = decrypt($_POST['website_ide'],'website');		
		else {
			$rs = aql::select("website { where domain = '{$_SERVER['SERVER_NAME']}' }");
			$website_id = $rs[0]['website_id'];
		}
		//echo $website_id;

		// Still don't have a website_id after checking config and website table so display a question to add it
		if (!$website_id) { 
?>					
			<h2><?=$_SERVER['SERVER_NAME']?></h2>
            <br>
            This Website Cannot Be Optimized Until it is Set Up in the System
            <br><br>
            Would You Like to Set it Up Now?&nbsp;&nbsp;&nbsp;&nbsp;<button id="set-up-website">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<button id="close">No</button>
			
<?		// The website exists... move forward to the check if website_page record is entered	
		} else {
			
			$aql="website_page { url_specific, nickname where page_path = '{$_POST['page_path']}' and website_id = {$website_id} }";
			$rs = aql::select($aql);
			$page = $rs[0];
			if ($page['website_page_id']) {
				// We have a website_page_record so load the form 
?>
				<div id="url_cb" style="margin-bottom:10px;">
<?
				if (!$page['url_specific']) {
?>
					<input type="checkbox" id="url_specific" website_page_ide="<?=$page['website_page_ide']?>" uri="<?=$_SERVER['PATH_INFO']?>" /> Make this page URL specific (cannot undo)
<?
				} else {
					echo $uri = $_SERVER['PATH_INFO'];
?>
					<input type="hidden" id="uri_enabled" value="<?=$uri?>" /> 
<?	
				}
?>
				</div>
<?
				include('pages/admin/seo/webpage/seo-webpage-form.php');
			}
			else {
				echo ("No Record Found");
				// We don't have the page record so we have to make it
				$data = array(
					'page_path'=>$_POST['page_path'],
					'website_id'=>$website_id,
					'start_mmdd'=>date('md'),
					'url'=>$_POST['url']
				);
				$insert = aql::insert('website_page',$data);
				$page['website_page_id'] = $insert[0]['website_page_id'];
				
				
				// Check if the record was entered correctly and display the form 
				if ($page['website_page_id']) {
?>
					<div id="url_cb" style="margin-bottom:10px;"><input type="checkbox" id="url_specific" website_page_ide="" /> Make this page URL specific (cannot undo)</div>
<?
					include('pages/admin/seo/webpage/seo-webpage-form.php');
				}
				else exit("There Was An Error Entering The Website Page Record.");
			}
		}
		$p->template('skybox','bottom');
	
?>

<script language="javascript">
	$(function() {
		$('#close').live('click',function() {
			History.back();
		});
		$('.seo-input').each(function(index, element) {
           	f = $(this).attr('field');
			var max_length = $(this).attr('max');
			var length = $(this).val().length;
			$('#'+f+'_char_count').html(length);
			if (length > max_length) $('#'+f+'_counter').css('color','#F00');
			else $('#'+f+'_counter').css('color','#000');
			$('#'+f+'_char_count').html(length);
        });
		
		
		$('.seo-input').live('keyup focusout', function(e) {
			f = $(this).attr('field');
			var max_length = $(this).attr('max');
			var length = $(this).val().length;
			$('#'+f+'_char_count').html(length);
			if (length > max_length) $('#'+f+'_counter').css('color','#F00');
			else $('#'+f+'_counter').css('color','#000');
			
			if (e.keyCode == 13 || e.type == 'focusout') {
				
				uri = $('#uri_enabled').val()
				v = $(this).val();
				w = $(this).attr('wp_id');
                s = $(this).attr('saved_id');
				$('#'+s).html('saving');
				$('#'+s).fadeOut('slow',function() {
					$.post('/admin/seo/webpage/ajax/save-seo', { field: f, value: v, wp_id: w, uri: uri }, function (data){
						$('#'+s).html(data);
						$('#'+s).fadeIn('slow');
					});
				});
			}
		});
		
		$('#set-up-website').live('click',function(){
			$.post('/admin/seo/website/set-up', function (data) {
				$('#skybox').html(data);
			});
		});
				
		$('#nickname_change').live('click', function() {
			$('#nickname').fadeOut();
			page_ide = $(this).attr('page_ide');
			$.post('/admin/seo/webpage/ajax/input', { field: 'nickname', website_page_ide: page_ide }, function(data) {
				$('#nickname').html(data);
				$('#nickname').fadeIn(800);
			});
		});
		
		$('#opt_phrase_change').live('click', function() {
			$('#opt_phrase').fadeOut();
			page_ide = $(this).attr('page_ide');
			$.post('/admin/seo/webpage/ajax/input', { field: 'opt_phrase', website_page_ide: page_ide }, function(data) {
				$('#opt_phrase').html(data);
				$('#opt_phrase').fadeIn(800);
			});
		});
		
		$('#input_field').live('focusout keyup',function(e) {
			if (e.keyCode == 13 || e.type == 'focusout') {
				f = $(this).attr('field');
				$('#'+f).fadeOut();
				val = $(this).val();
				page_ide = $(this).attr('page_ide');
				$.post('/admin/seo/webpage/ajax/change_field', { value: val, field: f, website_page_ide: page_ide }, function(data) {
					$('#'+f).html(data);
					$('#'+f).fadeIn(800);
				});
			}
		});
		
		$('#url_specific').live('click',function() {
			website_page_ide = $(this).attr('website_page_ide');
			$.post('/admin/seo/webpage/ajax/set_url_specific',{ website_page_ide: website_page_ide }, function(data) {
				$('#url_cb').html(data);	
			});
		});
		
	});
</script>

<? } ?>