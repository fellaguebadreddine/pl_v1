<?php
require_once("includes/initialiser.php");
if(!$session->is_logged_in()) {

	readresser_a("login.php");

}else{
	$user = Accounts::trouve_par_id($session->id_utilisateur);
	if (empty($user)) {
	$user = Client::trouve_par_id($session->id_utilisateur);
	}
	$accestype = array('administrateur','utilisateur');
	if( !in_array($user->type,$accestype)){ 
		//contenir_composition_template('simple_header.php'); 
		$msg_system ="vous n'avez pas le droit d'accéder a cette page <br/><img src='../images/AccessDenied.jpg' alt='Angry face' />";
		echo system_message($msg_system);
		// contenir_composition_template('simple_footer.php');
		exit();
	} 
}
?>
<?php
$titre = "ThreeSoft | Utilisateurs  ";
$active_menu = "Utilisateurs ";
$active_submenu = "Utilisateurs ";
$header = array('table','inputmask');
if ($user->type == "administrateur"){
	if (isset($_GET['action']) && $_GET['action'] =='list_user' ) {
        $action = 'list_user';
        $active_menu = "user";}
    else if (isset($_GET['action']) && $_GET['action'] =='add_user' ) {
	    $action = 'add_user';
	    $active_menu = "user";}
    else if (isset($_GET['action']) && $_GET['action'] =='edit_user' ) {
	    $action = 'edit_user';
	    $active_menu = "user";}
    else if (isset($_GET['action']) && $_GET['action'] =='open' ) {
       $action = 'open';}
    else if (isset($_GET['action']) && $_GET['action'] =='close_societe' ) {
        $action = 'close_societe';}
    if($action == 'open' ){
	$errors = array();
	// verification de données 	
        if (isset($_POST['id'])|| !empty($_POST['id'])){
	       $id = intval($_POST['id']);
			$nsociete = Societe::trouve_par_id($id);
        }elseif (isset($_GET['id'])|| !empty($_GET['id'])){
	       $id = intval($_GET['id']);
		   $nsociete = Societe::trouve_par_id($id);
        }
 	if ($nsociete){
   		// perform Update
	$session->set_societe($nsociete->id_societe);
	readresser_a("index.php");
	}
}
    if ($action=='close_societe') {
    $session->delete_societe();
    readresser_a("index.php");
    } // End of the main Submit conditional.
}       
    if ($user->type =='administrateur' or $user->type =='utilisateur'){
	require_once("header/header.php");
	require_once("header/navbar.php");
}
    else {
	readresser_a("profile_utils.php");
	 $personnes = Accounts::not_admin();
}
?>
<?php 
////////////////////// AJOUTER USER ////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['submit']) && $action == 'add_user'){
        $errors = array();
        
    ////////////// creat new objet user 

    $administrateur = new Accounts();
        
    $administrateur->user = htmlentities(trim($_POST['user']));
    $administrateur->nom = htmlentities(trim($_POST['nom']));
    $administrateur->prenom = htmlentities(trim($_POST['prenom']));
    $administrateur->id_societe = htmlentities(trim($_POST['id_societe']));
    $administrateur->active = 1;
    $administrateur->date_creation = mysql_datetime();
    // verification de données 	
    if (isset($_POST['password'])&& !empty($_POST['password'])){
        if($_POST['password'] != $_POST['rpassword']){
         $errors[]='Fausse confirmation de mot de passe.'; 
         }else{
          $administrateur->mot_passe = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
    }
    $administrateur->phash = $bd->escape_value(md5(rand(0,1000)));
    $administrateur->type = 'administrateur';

if (empty($errors)){
    if ($administrateur->existe()) {
        $msg_error = '<p > administrateur existe déja !!!</p><br />';			
            ;
        }else{
            $administrateur->save();
            
            $msg_positif = "<p>" . html_entity_decode($administrateur->nom) . " a été enregistré en attendant la validation</p><br />";
            
        }
        
        }else{
        // errors occurred
        $msg_error = '<h1> !! erreur </h1>';
        foreach ($errors as $msg) { // Print each error.
            $msg_error .= " - $msg<br />\n";
        }
        $msg_error .= '</p>';	  
        }
}
}
//////////////////////////////////// EDIT USER /////////////////////////////
if($action == 'edit_user' ){
    if ( (isset($_GET['id'])) && (is_numeric($_GET['id'])) ) { 
     $id = $_GET['id']; 
    $administrateur = Accounts:: trouve_par_id($id);
     } elseif ( (isset($_POST['id'])) &&(is_numeric($_POST['id'])) ) { 
         $id = $_POST['id'];
    $administrateur = Accounts:: trouve_par_id($id);
     } else { 
            $msg_error = '<p class="error">Cette page a été consultée par erreur</p>';
        } 
    if (isset($_POST['submit'])) {

    $errors = array();
        
    
    // new object admin administrateur
    $administrateur->user = htmlentities(trim($_POST['user']));
    $administrateur->nom = htmlentities(trim($_POST['nom']));
    $administrateur->prenom = htmlentities(trim($_POST['prenom']));
    $administrateur->id_societe = htmlentities(trim($_POST['id_societe']));
    $administrateur->active = 1;
    $administrateur->date_modif = mysql_datetime();
    // verification de données 	
    if (isset($_POST['password'])&& !empty($_POST['password'])){
        if($_POST['password'] != $_POST['rpassword']){
         $errors[]='Fausse confirmation de mot de passe.'; 
         }else{
          $administrateur->mot_passe = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
    }
    $administrateur->phash = $bd->escape_value(md5(rand(0,1000)));
    $administrateur->type = 'administrateur';

    
    $msg_positif= '';
     $msg_system= '';
    if (empty($errors)){
                    

         if ($administrateur->save()){
        $msg_positif .= '<p >  La banque ' . html_entity_decode($administrateur->user) . '  est modifié  avec succes </p><br />';
                                                    
                                                        
        }else{
        $msg_system .= "<h1>Une erreur dans le programme ! </h1>
                   <p  >  S'il vous plaît modifier à nouveau !!</p>";
        }
         
         }else{
        // errors occurred
        $msg_error = '<h1>erreur!</h1>';
        foreach ($errors as $msg) { // Print each error.
            $msg_error .= " - $msg<br />\n";
        }
        $msg_error .= '</p>';	  
        }
}		
    }
?>

	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="container-fluid">
			<!-- BEGIN PAGE CONTENT-->
			
			<!-- BEGIN PAGE HEADER-->
			
			<div class="page-bar">
				<ul class="page-breadcrumb breadcrumb">
                    <li>
                        <i class="fa fa-home"></i>
                        <a href="#">TVA</a>
                        <i class="fa fa-angle-right"></i>
                    </li>
                    <li><?php if ($action == 'add_tva') { ?>
                        <a href="tva.php?action=add_tva">Ajouter TVA</a> 
                        
                        
                    <?php }elseif ($action == 'list_tva') {
                        echo '<a href="tva.php?action=list_tva">Liste des TVA</a> ';
                    } elseif ($action == 'edit') {
                        echo '<a href="tva.php?action=edit_tva">Modifier TVA</a> ';
                    } ?>
                        
                    </li>
				</ul>

			</div>
			<!-- END PAGE HEADER-->
	    	<?php if ($user->type == 'administrateur') {
				if ($action == 'list_user') {?>
            <!--BEGIN LISTE USER -->
			
                <?php
                    $Accounts = Accounts::trouve_tous(); 
                    $cpt = 0; ?>
    
            <div class="row">
                    <div class="col-md-12">
    
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet light ">
                            <div class="portlet-title">
                            
                                
                                <div class="caption bold">
                                                <i class="fa  fa-user font-yellow "></i>Utilisateurs<span class="caption-helper"> (<?php echo count($Accounts);?>)</span>
                                            </div>
                                     
                                
                            </div>
                            <div class="table-toolbar">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group ">
                                                
                                                <a href="user.php?action=add_user"  class="btn btn-sm red">Nouveau utilisateur <i class="fa fa-plus"></i></a>
                                                
                                            </div>
                                        </div>
                                
                                    </div>
                                </div>
                                
                            <div class="table table-scrollable-borderless">
                                <table class="table table-striped table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Utilisateur
                                    </th>
                                    <th>
                                        Nom & prénom
                                    </th>
                                    <th>
                                        Type
                                    </th>
                                    <th>
                                        Societé 
                                    </th>
                                    <th>
                                    Date de création
                                    </th>
                                    <th>
                                    Etat
                                    </th>
                                    <th>
                                        #
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach($Accounts as $Account){
                                        $cpt ++;
                                    ?>
                                <tr>								
                                    <td>
                                    
                                                       
                                        <i class="fa  fa-user font-yellow "></i> <a href="societe.php?action=affiche_user&id=<?php if(isset($Account->id)) {echo $Account->id; }?>" class="" title="Afficher utilisateur"><?php if (isset($Account->user)) {
                                        echo $Account->user;
                                        } ?></a>
                                    </td>
                                    <td>
                                        <?php if(isset($Account->nom)) {echo '<i class="fa  fa-circle font-green-jungle "></i> '. $Account->nom_compler(); } ?>
                                        
                                    </td>
                                    <td>
                                        <?php if(isset($Account->type)) {echo $Account->type; } ?>
                                    </td>
                                    <td>
                                        <?php if ($Account->id_societe > 0 ) {
                                                $Societie = Societe::trouve_par_id($Account->id_societe);
                                                    if (isset($Societie->Dossier)) {
                                                            echo $Societie->Dossier;
                                                                }			
                                                }else { echo 'Super admin ( Toutes les societes)';}
                                                                    
                                                ?>
                                    </td>
                                    <td>
                                        <?php if(isset($Account->date_creation)) {echo fr_date2($Account->date_creation); } ?>
                                    </td>
                                    <td>
                                        
                                        <?php if ($Account->active == '1') { ?>
                                        <span class="label label-sm bg-green-jungle">
                                        Active </span> 
                                      <?php } else{  ?> 
                                        <span class="label label-sm label-danger">
                                        Désactive </span> 
                                    <?php    } ?> 
                                    </td>
                                    <td>
                                    <a href="user.php?action=edit_user&id=<?php echo $Account->id; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                    </td>
                                </tr>
    
                                <?php
                                    }
                                ?>
                                </tbody>
                                
                                </table>
                                
                                
                            </div>
                        </div>
                        
                    </div>
                </div>
                <!--END  LISTE USERS -->

                <!--BEGIN AJOUTER USER-->
			    <?php } else if ($action == 'add_user') {
				
                ?>
                <div class="row">
                        <div class="col-md-12">
                        <?php 
                            if (!empty($msg_error)){
                                echo error_message($msg_error); 
                            }elseif(!empty($msg_positif)){ 
                                echo positif_message($msg_positif);	
                            }elseif(!empty($msg_system)){ 
                                echo system_message($msg_system);
                        } ?>
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet light ">
                                <div class="portlet-title">
                                    <div class="caption bold">
                                        <i class="fa  fa-user font-yellow "></i>Ajouter Utilisateur
                                    </div>
                                </div>
                            
                                <div class="portlet-body form">
                                <form action="<?php echo $_SERVER['PHP_SELF']?>?action=add_user" method="POST" class="form-horizontal" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="" class="col-md-2 control-label">Utilisateur <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            <div class="input-icon">
                                                <i class="fa fa-user"></i>
                                                <input type="text" name="user" class="form-control" id="" placeholder="Utilisateur" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputPassword1" class="col-md-2 control-label">Mot de passe <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            <div class="input-icon">
                                                <i class="fa fa-lock"></i>
                                                <input type="password" id="password" name="password" class="form-control" placeholder="Mot de passe" required>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="inputPassword1" class="col-md-2 control-label">Confirmer mot de passe <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            <div class="input-icon">
                                                <i class="fa fa-lock"></i>
                                                <input type="password" id="password" name="rpassword" class="form-control" placeholder="Confirmer mot de passe" required>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-md-2 control-label">Nom <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            <div class="input-icon">
                                                <i class="fa fa-user"></i>
                                                <input type="text" name="nom" class="form-control" id="" placeholder="Nom" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-md-2 control-label">Prénom <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            <div class="input-icon">
                                                <i class="fa fa-user"></i>
                                                <input type="text" name="prenom" class="form-control" id="" placeholder="Prénom" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-md-2 control-label">Societe <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-4">
                                            
                                            <select class="form-control select2me"    name="id_societe" id="id_societe"  placeholder="" >
                                                <option value=""></option>
                                                    <?php $Societies = Societe::trouve_tous();
                                                    foreach ($Societies as $Societie){ ?>
                                                    <option value="<?php echo $Societie->id_societe ?>"><?php echo $Societie->Dossier; ?></option>
                                                    <?php } ?>
                                            </select>
                                            
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="form-group">
                                            <div class="col-md-offset-2 col-md-10">
                                                <button type="submit" name="submit" class="btn green">Ajouter</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <!--END  AJOUTER USER -->
    
                    <!--BEGIN EDIT USER-->
                    <?php } else if ($action == 'edit_user') {
                    
                    ?>
                    <div class="row">
                            <div class="col-md-12">
                            <?php 
                                if (!empty($msg_error)){
                                    echo error_message($msg_error); 
                                }elseif(!empty($msg_positif)){ 
                                    echo positif_message($msg_positif);	
                                }elseif(!empty($msg_system)){ 
                                    echo system_message($msg_system);
                            } ?>
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="portlet light ">
                                    <div class="portlet-title">
                                        <div class="caption bold">
                                            <i class="fa  fa-user font-yellow "></i>Edit Utilisateur
                                        </div>
                                    </div>
                                
                                    <div class="portlet-body form">
                                    <form action="<?php echo $_SERVER['PHP_SELF']?>?action=edit_user" method="POST" class="form-horizontal" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="" class="col-md-2 control-label">Utilisateur <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                <div class="input-icon">
                                                    <i class="fa fa-user"></i>
                                                    <input type="text" name="user" class="form-control" value="<?php if(isset($administrateur->user)){echo $administrateur->user;}?>" id="" placeholder="Utilisateur" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPassword1" class="col-md-2 control-label">Mot de passe <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                <div class="input-icon">
                                                    <i class="fa fa-lock"></i>
                                                    <input type="password" id="password" name="password" value="<?php if(isset($administrateur->password)){echo $administrateur->password;}?>" class="form-control" placeholder="Mot de passe" required>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPassword1" class="col-md-2 control-label">Confirmer mot de passe <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                <div class="input-icon">
                                                    <i class="fa fa-lock"></i>
                                                    <input type="password" id="password" name="rpassword" class="form-control" placeholder="Confirmer mot de passe" required>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="col-md-2 control-label">Nom <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                <div class="input-icon">
                                                    <i class="fa fa-user"></i>
                                                    <input type="text" name="nom" value="<?php if(isset($administrateur->nom)){echo $administrateur->nom;}?>" class="form-control" id="" placeholder="Nom" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="col-md-2 control-label">Prénom <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                <div class="input-icon">
                                                    <i class="fa fa-user"></i>
                                                    <input type="text" name="prenom" value="<?php if(isset($administrateur->prenom)){echo $administrateur->prenom;}?>" class="form-control" id="" placeholder="Prénom" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="col-md-2 control-label">Societe <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-4">
                                                
                                                <select class="form-control select2me"    name="id_societe" id="id_societe"  placeholder="" >
                                                    <option value=""></option>
                                                        <?php $Societies = Societe::trouve_tous();
                                                        foreach ($Societies as $Societie){ ?>
                                                        <option <?php if ($administrateur->id_societe == $Societie->id_societe) {echo "selected";}?> value="<?php echo $Societie->id_societe ?>"><?php echo $Societie->Dossier; ?></option>
                                                        <?php } ?>
                                                </select>
                                                
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <div class="form-group">
                                                <div class="col-md-offset-2 col-md-10">
                                                    <button type="submit" name="submit" class="btn green">Modifier</button>
                                                    <?php echo '<input type="hidden" name="id" value="' .$id . '" />';?>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <!--END  EDIT USER -->

			    <?php }}?>
		        </div>
	        </div>
	    </div>
	</div>
	<!-- END CONTENT -->
   </div>

<!-- END CONTAINER -->
<?php
require_once("footer/footer.php");
?>