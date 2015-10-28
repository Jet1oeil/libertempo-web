<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either 
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/
namespace config;

/**
 * Regroupement des fonctions liées à la configuration
 *
 */
class Fonctions
{
    public static function commit_vider_table_logs($session, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;
        $PHP_SELF=$_SERVER['PHP_SELF'];

        $sql_delete="TRUNCATE TABLE conges_logs ";
        $ReqLog_delete = \includes\SQL::query($sql_delete);

        // ecriture de cette action dans les logs
        $comment_log = "effacement des logs de php_conges ";
        log_action(0, "", "", $comment_log, $DEBUG);

        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";
        if($session=="")
            redirect( ROOT_PATH .'config/index.php?onglet=logs');
        else
            redirect( ROOT_PATH .'config/index.php?session='.$session . '&onglet=logs');
    }

    public static function confirmer_vider_table_logs($session, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;
        $PHP_SELF=$_SERVER['PHP_SELF'];

        echo "<center>\n";
        echo "<br><h2>". _('confirm_vider_logs') ."</h2><br>\n";
        echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
        echo "<input type=\"hidden\" name=\"action\" value=\"commit_suppr_logs\">\n";
        echo "<input type=\"submit\" value=\"". _('form_delete_logs') ."\">\n";
        echo "</form>\n" ;
        echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
        echo "<input type=\"submit\" value=\"". _('form_cancel') ."\">\n";
        echo "</form>\n" ;
        echo "</center>\n";
    }

    public static function affichage($login_par, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        //requête qui récupère les logs
        $sql1 = "SELECT log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date FROM conges_logs ";
        if($login_par!="")
            $sql1 = $sql1." WHERE log_user_login_par = '$login_par' ";
        $sql1 = $sql1." ORDER BY log_date";

        $ReqLog1 = \includes\SQL::query($sql1);

        if($ReqLog1->num_rows !=0)
        {

            if($session=="")
                echo "<form action=\"$PHP_SELF?onglet=logs\" method=\"POST\"> \n";
            else
                echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\"> \n";

            echo "<br>\n";
            echo "<table class=\"table table-hover table-stripped table-condensed\">\n";

            echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_les_logs_par') ."</td>";
            if($login_par!="")
                echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_tous_les_logs') ." <a href=\"$PHP_SELF?session=$session&onglet=logs\">". _('voir_tous_les_logs') ."</a></td>";
            echo "<tr><td class=\"histo\" colspan=\"5\">&nbsp;</td>";

            // titres
            echo "<tr>\n";
            echo "<td>". _('divers_date_maj_1') ."</td>\n";
            echo "<td>". _('divers_fait_par_maj_1') ."</td>\n";
            echo "<td>". _('divers_pour_maj_1') ."</td>\n";
            echo "<td>". _('divers_comment_maj_1') ."</td>\n";
            echo "<td>". _('divers_etat_maj_1') ."</td>\n";
            echo "</tr>\n";

            // affichage des logs
            while ($data = $ReqLog1->fetch_array())
            {
                $log_login_par = $data['log_user_login_par'];
                $log_login_pour = $data['log_user_login_pour'];
                $log_log_etat = $data['log_etat'];
                $log_log_comment = $data['log_comment'];
                $log_log_date = $data['log_date'];

                echo "<tr>\n";
                echo "<td>$log_log_date</td>\n";
                echo "<td><a href=\"$PHP_SELF?session=$session&onglet=logs&login_par=$log_login_par\"><b>$log_login_par</b></a></td>\n";
                echo "<td>$log_login_pour</td>\n";
                echo "<td>$log_log_comment</td>\n";
                echo "<td>$log_log_etat</td>\n";
                echo "</tr>\n";
            }

            echo "</table>\n";

            // affichage du bouton pour vider les logs
            echo "<input type=\"hidden\" name=\"action\" value=\"suppr_logs\">\n";
            echo "<input class=\"btn btn-danger\" type=\"submit\"  value=\"". _('form_delete_logs') ."\"><br>";
            echo "</form>\n";
        }
        else
            echo  _('no_logs_in_db') ."><br>";
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function logModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);

        if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


        /*** initialisation des variables ***/
        /************************************/

        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action         = getpost_variable('action', "") ;
        $login_par      = getpost_variable('login_par', "") ;

        /*************************************/

        // header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);


        if($action=="suppr_logs")
            \config\Fonctions::confirmer_vider_table_logs($session, $DEBUG);
        elseif($action=="commit_suppr_logs")
            \config\Fonctions::commit_vider_table_logs($session, $DEBUG);
        else
            \config\Fonctions::affichage($login_par, $session, $DEBUG);
        // bottom();
    }

    public static function commit_modif($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";


        // update de la table
        foreach($tab_new_values as $nom_mail => $tab_mail)
        {
            $subject = addslashes($tab_mail['subject']);
            $body = addslashes($tab_mail['body']) ;
            $req_update='UPDATE conges_mail SET mail_subject=\''.$subject.'\', mail_body=\''.$body.'\' WHERE mail_nom="'. \includes\SQL::quote($nom_mail).'" ';
            $result1 = \includes\SQL::query($req_update);
        }
        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

        $comment_log = "configuration des mails d\'alerte";
        log_action(0, "", "", $comment_log, $DEBUG);

        if( $DEBUG )
            echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
        else
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
    }

    public static function test_config($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";

        // update de la table
        $mail_array             = find_email_adress_for_user($_SESSION['userlogin'], $DEBUG);
        $mail_sender_name       = $mail_array[0];
        $mail_sender_addr       = $mail_array[1];
        constuct_and_send_mail("valid_conges", "Test email", $mail_sender_addr, $mail_sender_name, $mail_sender_addr, "test", $DEBUG);
        //  echo "<p>Mail sent</p>"; exit(0);

        echo "<span class = \"messages\">". _('Mail_test_ok') ."</span><br>";

        $comment_log = "test d\'envoi mail d\'alerte";
        log_action(0, "", "", $comment_log, $DEBUG);

        if( $DEBUG )
            echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
        else
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
    }

    public static function affichage_config_mail($tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=mail";
        else
            $URL = "$PHP_SELF?session=$session&onglet=mail";

        /**************************************/
        // affichage du titre
        echo "<div class=\"alert alert-info\"> ". _('config_mail_alerte_config') ."</div>\n";
        /**************************************/

        // affichage de la liste des type d'absence existants

        //requête qui récupère les informations de la table conges_type_absence
        $sql1 = "SELECT * FROM conges_mail ";
        $ReqLog1 = \includes\SQL::query($sql1);

        echo "<form method=\"POST\" action=\"$URL\"> \n";
        echo "<input type=\"hidden\" name=\"action\" value=\"test\" /> \n";
        echo "<input class=\"btn btn-success\" type=\"submit\"  value=\"". _('test_mail_config') ."\"><br>\n";
        echo _('test_mail_comment');
        echo "</form> \n";

        echo "    <form action=\"$URL\" method=\"POST\"> \n";
        while ($data = $ReqLog1->fetch_array())
        {
            $mail_nom = stripslashes($data['mail_nom']);
            $mail_subject = stripslashes($data['mail_subject']);
            $mail_body = stripslashes($data['mail_body']);

            $legend =$mail_nom ;
            $key = $mail_nom."_comment";
            $comment =  _($key)  ;

            echo "<br>\n";
            echo "<table>\n";
            echo "<tr><td>\n";
            echo "    <fieldset class=\"cal_saisie\">\n";
            echo "    <legend class=\"boxlogin\">$legend</legend>\n";
            echo "    <i>$comment</i><br><br>\n";
            echo "    <table>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_subject') ."</b></td>\n";
            echo "    	<td class=\"config\"><input class=\"form-control\" type=\"text\" size=\"80\" name=\"tab_new_values[$mail_nom][subject]\" value=\"$mail_subject\"></td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_body') ."</b></td>\n";
            echo "    	<td class=\"config\"><textarea class=\"form-control\" rows=\"6\" cols=\"80\" name=\"tab_new_values[$mail_nom][body]\" value=\"$mail_body\">$mail_body</textarea></td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "    	<td class=\"config\">&nbsp;</td>\n";
            echo "    	<td class=\"config\">\n";
            echo "    		<i>". _('mail_remplace_url_accueil_comment') ."<br>\n";
            echo "    		". _('mail_remplace_sender_name_comment') ."<br>\n";
            echo "    		". _('mail_remplace_destination_name_comment') ."<br>\n";
            echo "    		". _('mail_remplace_nb_jours') ."<br>\n";
            echo "    		". _('mail_remplace_date_debut') ."<br>\n";
            echo "    		". _('mail_remplace_date_fin') ."<br>\n";
            echo "    		". _('mail_remplace_commentaire') ."<br>\n";
            echo "    		". _('mail_remplace_type_absence') ."<br>\n";
            echo "    		". _('mail_remplace_retour_ligne_comment') ."</i>\n";
            echo "    	</td>\n";
            echo "    </tr>\n";

            echo "    </table>\n";
            echo "</td></tr>\n";
            echo "</table>\n";
        }

        echo "    <input type=\"hidden\" name=\"action\" value=\"modif\">\n";
        echo "<hr/>\n";
        echo "    <input class=\"btn btn-success\" type=\"submit\"  value=\"". _('form_save_modif') ."\"><br>\n";
        echo "    </form>\n";
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function mailModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);


        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');

        /*************************************/

        if($DEBUG)
        {
            print_r($tab_new_values); echo "<br>\n";
            echo "$action<br>\n";
        }

        /*********************************/
        /*********************************/

        if($action=="modif")
            \config\Fonctions::commit_modif($tab_new_values, $session, $DEBUG);
        if($action=="test")
            \config\Fonctions::test_config($tab_new_values, $session, $DEBUG);


        \config\Fonctions::affichage_config_mail($tab_new_values, $session, $DEBUG);

        /*********************************/
        /*********************************/

        // bottom();
    }

    // recup l'id de la derniere absence (le max puisque c'est un auto incrément)
    public static function get_last_absence_id($DEBUG=FALSE)
    {
        $req_1="SELECT MAX(ta_id) FROM conges_type_absence ";
        $res_1 = \includes\SQL::query($req_1);
        $row_1 = $res_1->fetch_row();
        if(!$row_1)
            return 0;     // si la table est vide, on renvoit 0
        else
            return $row_1[0];
    }

    //
    // cree un tableau à partir des valeurs du enum(...) d'un champ mysql (cf structure des tables)
    //    $table         = nom de la table sql
    //    $column        = nom du champ sql
    public static function get_tab_from_mysql_enum_field($table, $column, $DEBUG=FALSE)
    {

        $tab=array();
        $req_enum = "DESCRIBE $table $column";
        $res_enum = \includes\SQL::query($req_enum);

        while ($row_enum = $res_enum->fetch_array())
        {
            $sql_type=$row_enum['Type'];
            // exemple : enum('autre','labo','fonction','personne','web', ....
            $liste_enum = strstr($sql_type, '(');
            $liste_enum = substr($liste_enum, 1);    // on vire le premier caractere
            $liste_enum = substr($liste_enum, 0, strlen($liste_enum)-1);    // on vire le dernier caractere
            $option = strtok($liste_enum,"','");
            while ($option)
            {
                $tab[]=$option;
                $option = strtok("','");
            }
        }

        return $tab;
    }

    public static function commit_ajout(&$tab_new_values, $session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=type_absence";
        else
            $URL = "$PHP_SELF?session=$session&onglet=type_absence";
        if( $DEBUG ) { echo "URL = $URL<br>\n"; }

        // verif de la saisie
        $erreur=FALSE ;
        // verif si pas de " ' , . ; % ?
        if( (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['libelle'])) || (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['short_libelle'])) )
        {
            echo "<br> ". _('config_abs_saisie_not_ok') ." : ". _('config_abs_bad_caracteres') ."  \" \' , . ; % ? <br>\n";
            $erreur=TRUE;
        }
        // verif si les champs sont vides
        if( (strlen($tab_new_values['libelle'])==0) || (strlen($tab_new_values['short_libelle'])==0) || (strlen($tab_new_values['type'])==0) )
        {
            echo "<br> ". _('config_abs_saisie_not_ok') ." : ". _('config_abs_champs_vides') ." <br>\n";
            $erreur=TRUE;
        }


        if($erreur)
        {
            echo "<br>\n";
            echo "<form action=\"$URL\" method=\"POST\"> \n";
            echo "<input type=\"hidden\" name=\"id_to_update\" value=\"$id_to_update\">\n";
            echo "<input type=\"hidden\" name=\"tab_new_values[libelle]\" value=\"".$tab_new_values['libelle']."\">\n";
            echo "<input type=\"hidden\" name=\"tab_new_values[short_libelle]\" value=\"".$tab_new_values['short_libelle']."\">\n";
            echo "<input type=\"hidden\" name=\"tab_new_values[type]\" value=\"".$tab_new_values['type']."\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\" >\n";
            echo "</form>\n";
            echo "<br><br>\n";
        }
        else
        {
            // ajout dans la table conges_type_absence
            $req_insert1="INSERT INTO conges_type_absence (ta_libelle, ta_short_libelle, ta_type) " .
                "VALUES ('".$tab_new_values['libelle']."', '".$tab_new_values['short_libelle']."', '".$tab_new_values['type']."') ";
            $result1 = \includes\SQL::query($req_insert1);

            // on recup l'id de l'absence qu'on vient de créer
            $new_abs_id = \config\Fonctions::get_last_absence_id($DEBUG);

            if($new_abs_id!=0)
            {
                // ajout dans la table conges_solde_user (pour chaque user !!)(si c'est un conges, pas si c'est une absence)
                if( ($tab_new_values['type']=="conges") || ($tab_new_values['type']=="conges_exceptionnels") )
                {
                    // recup de users :
                    $sql_users="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='conges' AND u_login!='admin' " ;

                    $ReqLog_users = \includes\SQL::query($sql_users);

                    while ($resultat1 = $ReqLog_users->fetch_array())
                    {
                        $current_login=$resultat1["u_login"];

                        $req_insert2="INSERT INTO conges_solde_user (su_login, su_abs_id, su_nb_an, su_solde, su_reliquat) " .
                            "VALUES ('$current_login', $new_abs_id, 0, 0, 0) ";
                        $result2 = \includes\SQL::query($req_insert2);
                    }
                }
                echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";
            }

            $comment_log = "config : ajout_type_absence : ".$tab_new_values['libelle']."  (".$tab_new_values['short_libelle'].") (type : ".$tab_new_values['type'].") ";
            log_action(0, "", "", $comment_log, $DEBUG);

            if( $DEBUG )
                echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
            else
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
        }
    }

    public static function commit_suppr($session, $id_to_update, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=type_absence";
        else
            $URL = "$PHP_SELF?session=$session&onglet=type_absence";
        if( $DEBUG ) { echo "URL = $URL<br>\n"; }

        // delete dans la table conges_type_absence
        $req_delete1='DELETE FROM conges_type_absence WHERE ta_id='. \includes\SQL::quote($id_to_update);
        $result1 = \includes\SQL::query($req_delete1);

        // delete dans la table conges_solde_user
        $req_delete2='DELETE FROM conges_solde_user WHERE su_abs_id='.\includes\SQL::quote($id_to_update);
        $result2 = \includes\SQL::query($req_delete2);

        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

        $comment_log = "config : supprime_type_absence ($id_to_update) ";
        log_action(0, "", "", $comment_log,$DEBUG);

        if( $DEBUG )
            echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
        else
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
    }

    public static function supprimer($session, $id_to_update, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=type_absence";
        else
            $URL = "$PHP_SELF?session=$session&onglet=type_absence";


        // verif si pas de periode de ce type de conges !!!
        //requête qui récupère les informations de la table conges_periode
        $sql1 = 'SELECT p_num FROM conges_periode WHERE p_type="'. \includes\SQL::quote($id_to_update).'"';
        $ReqLog1 = \includes\SQL::query($sql1);

        $count= ($ReqLog1->num_rows) ;

        if( $count!=0 )
        {
            echo "<center>\n";
            echo "<br> ". _('config_abs_suppr_impossible') ."<br>\n". _('config_abs_already_used') ." <br>\n";

            echo "<br>\n";
            echo "<form action=\"$URL\" method=\"POST\"> \n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\" >\n";
            echo "</form>\n";
            echo "<br><br>\n";
            echo "</center>\n";
        }
        else
        {
            // recup dans un tableau de tableau les infos des types de conges et absences
            $tab_type_abs = recup_tableau_tout_types_abs($DEBUG);

            echo "<center>\n";
            echo "<br>\n";
            echo  _('config_abs_confirm_suppr_of') ."  <b>\" ".$tab_type_abs[$id_to_update]['libelle']." \"</b>\n";
            echo "<br>\n";
            echo "<form action=\"$URL\" method=\"POST\"> \n";
            echo "<input type=\"hidden\" name=\"action\" value=\"commit_suppr\">\n";
            echo "<input type=\"hidden\" name=\"id_to_update\" value=\"$id_to_update\">\n";
            echo "<input type=\"submit\"  value=\"". _('form_supprim') ."\">\n";
            echo "</form>\n";

            echo "<br>\n";
            echo "<form action=\"$URL\" method=\"POST\"> \n";
            echo "<input type=\"submit\" value=\"". _('form_annul') ."\" >\n";
            echo "</form>\n";
            echo "<br><br>\n";
            echo "</center>\n";
        }
    }

    public static function commit_modif_absence(&$tab_new_values, $session, $id_to_update, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF";
        else
            $URL = "$PHP_SELF?session=$session";


        // verif de la saisie
        $erreur=FALSE ;
        // verif si pas de " ' , . ; % ?
        if( (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['libelle'])) || (preg_match('/.*[?%;.,"\'].*$/', $tab_new_values['short_libelle'])) )
        {
            echo "<br> ". _('config_abs_saisie_not_ok') ." : ". _('config_abs_bad_caracteres') ."  \" \' , . ; % ? <br>\n";
            $erreur=TRUE;
        }
        // verif si les champs sont vides
        if( (strlen($tab_new_values['libelle'])==0) || (strlen($tab_new_values['short_libelle'])==0) )
        {
            echo "<br> ". _('config_abs_saisie_not_ok') ." : ". _('config_abs_champs_vides') ." <br>\n";
            $erreur=TRUE;
        }

        if($erreur)
        {
            echo "<br>\n";
            if($session=="")
                echo "<form action=\"$PHP_SELF?onglet=type_absence\" method=\"POST\"> \n";
            else
                echo "<form action=\"$PHP_SELF?session=$session&onglet=type_absence\" method=\"POST\"> \n";
            echo "<input type=\"hidden\" name=\"action\" value=\"modif\">\n";
            echo "<input type=\"hidden\" name=\"id_to_update\" value=\"$id_to_update\">\n";
            echo "<input type=\"hidden\" name=\"tab_new_values[libelle]\" value=\"".$tab_new_values['libelle']."\">\n";
            echo "<input type=\"hidden\" name=\"tab_new_values[short_libelle]\" value=\"".$tab_new_values['short_libelle']."\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\" >\n";
            echo "</form>\n";
            echo "<br><br>\n";
        }
        else
        {
            // update de la table
            $req_update='UPDATE conges_type_absence SET ta_libelle=\''.$tab_new_values['libelle'].'\', ta_short_libelle=\''.$tab_new_values['short_libelle'].'\' WHERE ta_id="'. \includes\SQL::quote($id_to_update).'" ';
            $result1 = \includes\SQL::query($req_update);

            echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

            $comment_log = "config : modif_type_absence ($id_to_update): ".$tab_new_values['libelle']."  (".$tab_new_values['short_libelle'].") ";
            log_action(0, "", "", $comment_log, $DEBUG);

            if( $DEBUG )
                echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
            else
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";
        }
    }

    public static function modifier(&$tab_new_values, $session, $id_to_update, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=type_absence";
        else
            $URL = "$PHP_SELF?session=$session&onglet=type_absence";

        /**************************************/
        // affichage du titre
        echo "<br><center><H1> ". _('config_abs_titre') ."</H1></center>\n";
        echo "<br>\n";

        // recup des infos du type de conges / absences
        $sql_cong='SELECT ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence WHERE ta_id = '. \includes\SQL::quote($id_to_update);

        $ReqLog_cong = \includes\SQL::query($sql_cong);

        if($resultat_cong = $ReqLog_cong->fetch_array())
        {
            $sql_type=$resultat_cong['ta_type'];
            $sql_libelle= $resultat_cong['ta_libelle'];
            $sql_short_libelle= $resultat_cong['ta_short_libelle'];
        }

        // mise en place du formulaire
        echo "<form action=\"$URL\" method=\"POST\"> \n";

        $text_libelle ="<input class=\"form-control\" type=\"text\" name=\"tab_new_values[libelle]\" size=\"20\" maxlength=\"20\" value=\"$sql_libelle\" >";
        $text_short_libelle ="<input class=\"form-control\" type=\"text\" name=\"tab_new_values[short_libelle]\" size=\"3\" maxlength=\"3\" value=\"$sql_short_libelle\" >";

        // affichage
        echo "<table cellpadding=\"2\" class=\"tablo\" >\n";
        echo "    <tr>\n";
        echo "    <td><b><u>". _('config_abs_libelle') ."</b></u></td>\n";
        echo "    <td><b><u>". _('config_abs_libelle_short') ."</b></u></td>\n";
        echo "    <td>". _('divers_type') ."</td>\n";
        echo "    </tr>\n";
        echo "    <tr><td><b>$sql_libelle</b></td><td>$sql_short_libelle</td><td>$sql_type</td></tr>\n";
        echo "    <tr><td><b>$text_libelle</b></td><td>$text_short_libelle</td><td></td></tr>\n";

        echo "</table>\n";
        echo "<br>\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"commit_modif\">\n";
        echo "<input type=\"hidden\" name=\"id_to_update\" value=\"$id_to_update\">\n";
        echo "<input type=\"submit\"  value=\"". _('form_modif') ."\">\n";
        echo "</form>\n";

        echo "<br>\n";
        echo "<form action=\"$URL\" method=\"POST\"> \n";
        echo "<input type=\"submit\" value=\"". _('form_annul') ."\" >\n";
        echo "</form>\n";
        echo "<br><br>\n";
    }

    public static function affichage_absence($tab_new_values,$session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF?onglet=type_absence";
        else
            $URL = "$PHP_SELF?session=$session&onglet=type_absence";

        /**************************************/
        // affichage du titre
        echo "<h1>". _('config_abs_titre') ."</h1>\n";
        /**************************************/

        // affiche_bouton_retour($session);


        // affichage de la liste des type d'absence existants

        $tab_enum = \config\Fonctions::get_tab_from_mysql_enum_field("conges_type_absence", "ta_type", $DEBUG);

        foreach($tab_enum as $ta_type)
        {
            if( ($ta_type=="conges_exceptionnels") &&  ($_SESSION['config']['gestion_conges_exceptionnels']==FALSE))
            {
            }
            else
            {
                $divers_maj_1 = 'divers_' . $ta_type . '_maj_1';
                $config_abs_comment = 'config_abs_comment_' . $ta_type;

                $legend= _($divers_maj_1)  ;
                $comment= _($config_abs_comment)  ;

                echo "<h2>$legend</h2>\n";
                echo "<p>$comment</p>\n";

                //requête qui récupère les informations de la table conges_type_absence
                $sql1 = 'SELECT * FROM conges_type_absence WHERE ta_type = "'. \includes\SQL::quote($ta_type).'"';
                $ReqLog1 = \includes\SQL::query($sql1);

                if($ReqLog1->num_rows !=0)
                {
                    echo "    <table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
                    echo "    <tr>\n";
                    echo "    <th>" . _('config_abs_libelle') . "</th>\n";
                    echo "    <th>" . _('config_abs_libelle_short') . "</th>\n";
                    echo "    <th></th>\n";
                    echo "    </tr>\n";

                    while ($data = $ReqLog1->fetch_array())
                    {
                        $ta_id = $data['ta_id'];
                        $ta_libelle = $data['ta_libelle'];
                        $ta_short_libelle = $data['ta_short_libelle'];

                        if($session=="")
                        {
                            $text_modif="<a href=\"$PHP_SELF?action=modif&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>";
                            $text_suppr="<a href=\"$PHP_SELF?action=suppr&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>";
                        }
                        else
                        {
                            $text_modif="<a href=\"$PHP_SELF?session=$session&action=modif&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>";
                            $text_suppr="<a href=\"$PHP_SELF?session=$session&action=suppr&id_to_update=$ta_id&onglet=type_absence\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>";
                        }

                        echo "    <tr><td><strong>$ta_libelle</strong></td><td>$ta_short_libelle</td><td class=\"action\">$text_modif&nbsp;$text_suppr</td></tr>\n";
                    }

                    echo "    </table>\n";
                    echo "<hr/>\n";
                }
            }
        }


        /**************************************/
        // saisie de nouveaux type d'absence
        echo "<h2>" . _('config_abs_add_type_abs') . "</h2>\n";
        echo "<p>" . _('config_abs_add_type_abs_comment') . "</p>\n";
        echo "    <form action=\"$URL\" method=\"POST\"> \n";
        echo "    <table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
        echo "    <tr>\n";
        echo "    <th>". _('config_abs_libelle') ."</th>\n";
        echo "    <th>". _('config_abs_libelle_short') ."</th>\n";
        echo "    <th>". _('divers_type') ."</th>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        $new_libelle = ( isset($tab_new_values['libelle']) ? $tab_new_values['libelle'] : "" );
        $new_short_libelle = ( isset($tab_new_values['short_libelle']) ? $tab_new_values['short_libelle'] : "" ) ;
        $new_type = ( isset($tab_new_values['type']) ? $tab_new_values['type'] : "" ) ;
        echo "    <td><input class=\"form-control\" type=\"text\" name=\"tab_new_values[libelle]\" size=\"20\" maxlength=\"20\" value=\"$new_libelle\" ></td>\n";
        echo "    <td><input class=\"form-control\" type=\"text\" name=\"tab_new_values[short_libelle]\" size=\"3\" maxlength=\"3\" value=\"$new_short_libelle\" ></td>\n";
        echo "    <td>\n";

        echo "<select class=\"form-control\" name=tab_new_values[type]>\n";

        foreach($tab_enum as $option)
        {
            if( ($option=="conges_exceptionnels") &&  ($_SESSION['config']['gestion_conges_exceptionnels']==FALSE))
            {
            }
            else
            {
                if($option==$new_type)
                    echo "<option selected>$option</option>\n";
                else
                    echo "<option>$option</option>\n";
            }
        }

        echo "</select>\n";

        echo "    </td></tr>\n";
        echo "    </table>\n";

        echo "    <input type=\"hidden\" name=\"action\" value=\"new\">\n";
        echo "<hr/>\n";
        echo "<input type=\"submit\" class=\"btn btn-success\" value=\"". _('form_ajout') ."\"><br>\n";
        echo "</form>\n";
    }

    /**
     * Encapsule le comportement du module de configuration des types de congés
     *
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function typeAbsenceModule($DEBUG = false)
    {
        $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;

        if (file_exists(CONFIG_PATH .'config_ldap.php'))
            include_once CONFIG_PATH .'config_ldap.php';

        // include_once ROOT_PATH .'fonctions_conges.php' ;
        // include_once INCLUDE_PATH .'fonction.php';
        if(!isset($_SESSION['config']))
            $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
        include_once INCLUDE_PATH .'session.php';

        //$DEBUG = TRUE ;
        $DEBUG = FALSE ;

        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);



        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action         = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');
        $id_to_update   = getpost_variable('id_to_update');

        /*************************************/

        if($DEBUG)
        {
            print_r($tab_new_values); echo "<br>\n";
            echo "$action<br>\n";
            echo "$id_to_update<br>\n";
        }


        /*********************************/
        /*********************************/

        if($action=="new")
            \config\Fonctions::commit_ajout($tab_new_values,$session, $DEBUG);
        elseif($action=="modif")
            \config\Fonctions::modifier($tab_new_values, $session, $id_to_update, $DEBUG);
        elseif($action=="commit_modif")
            \config\Fonctions::commit_modif_absence($tab_new_values, $session, $id_to_update, $DEBUG);
        elseif($action=="suppr")
            \config\Fonctions::supprimer($session, $id_to_update, $DEBUG);
        elseif($action=="commit_suppr")
            \config\Fonctions::commit_suppr($session, $id_to_update, $DEBUG);
        else
            \config\Fonctions::affichage_absence($tab_new_values, $session, $DEBUG);

        /*********************************/
        /*********************************/

        bottom();
    }

    public static function commit_saisie(&$tab_new_values, $session, $DEBUG=FALSE)
    {
        //$DEBUG=TRUE;
        $PHP_SELF=$_SERVER['PHP_SELF'];

        if($session=="")
            $URL = "$PHP_SELF";
        else
            $URL = "$PHP_SELF?session=$session";

        $timeout=2 ;  // temps d'attente pour rafraichir l'écran après l'update !

        if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n"; }

        foreach($tab_new_values as $key => $value )
        {
            // CONTROLE gestion_conges_exceptionnels
            // si désactivation les conges exceptionnels, on verif s'il y a des conges exceptionnels enregistres ! si oui : changement impossible !
            if(($key=="gestion_conges_exceptionnels") && ($value=="FALSE") )
            {
                $sql_abs="SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type='conges_exceptionnels' ";
                $ReqLog_abs = \includes\SQL::query($sql_abs);

                if($ReqLog_abs->num_rows !=0)
                {
                    echo "<b>". _('config_abs_desactive_cong_excep_impossible') ."</b><br>\n";
                    $value = "TRUE" ;
                    $timeout=5 ;
                }
            }

            // CONTROLE jour_mois_limite_reliquats
            // si modif de jour_mois_limite_reliquats, on verifie le format ( 0 ou jj-mm) , sinon : changement impossible !
            if( ($key=="jour_mois_limite_reliquats") && ($value!= "0") )
            {
                $t=explode("-", $value);
                if(checkdate($t[1], $t[0], date("Y"))==FALSE)
                {
                    echo "<b>". _('config_jour_mois_limite_reliquats_modif_impossible') ."</b><br>\n";
                    $sql_date="SELECT conf_valeur FROM conges_config WHERE conf_nom='jour_mois_limite_reliquats' ";
                    $ReqLog_date = \includes\SQL::query($sql_date);
                    $data = $ReqLog_date->fetch_row();
                    $value = $data[0] ;
                    $timeout=5 ;
                }
            }

            if(preg_match("/_installed$/",$key) && ($value=="1"))
            {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                install_plugin($plugin);
            }
            elseif(preg_match("/_installed$/",$key) && ($value=="0"))
            {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                uninstall_plugin($plugin);
            }
            if(preg_match("/_activated$/",$key) && ($value=="1"))
            {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                activate_plugin($plugin);
            }
            elseif(preg_match("/_activated$/",$key) && ($value=="0"))
            {
                $plugin = explode("_",$key);
                $plugin = $plugin[0];
                disable_plugin($plugin);
            }

            // Mise à jour
            $sql2 = 'UPDATE conges_config SET conf_valeur = \''.addslashes($value).'\' WHERE conf_nom ="'. \includes\SQL::quote($key).'" ';
            $ReqLog2 = \includes\SQL::query($sql2);
        }

        $_SESSION['config']=init_config_tab();      // on re-initialise le tableau des variables de config

        // enregistrement dans les logs
        $comment_log = "nouvelle configuration de php_conges ";
        log_action(0, "", "", $comment_log, $DEBUG);

        echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"$timeout; URL=$URL\">";
    }

    public static function affichage_configuration($session, $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        // affiche_bouton_retour($session);


        // affichage de la liste des variables

        if($session=="")
            echo "<form action=\"$PHP_SELF\" method=\"POST\"> \n";
        else
            echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\"> \n";
        echo "<input type=\"hidden\" name=\"action\" value=\"commit\">\n";

        //requête qui récupère les informations de config
        $sql1 = "SELECT * FROM conges_config ORDER BY conf_groupe ASC";
        $ReqLog1 = \includes\SQL::query($sql1);

        $old_groupe="";
        while ($data =$ReqLog1->fetch_array())
        {
            $conf_nom = $data['conf_nom'];
            $conf_valeur = $data['conf_valeur'];
            $conf_groupe = $data['conf_groupe'];
            $conf_type = strtolower($data['conf_type']);
            $conf_commentaire = strtolower($data['conf_commentaire']);

            // changement de groupe de variables
            if($old_groupe != $conf_groupe)
            {
                if($old_groupe!="")
                {
                    echo "</td></tr>\n";
                    echo "<tr><td align=\"right\">\n";
                    echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
                    echo "</td></tr>\n";
                    echo "</table>\n";
                }
                echo "<br>\n";
                echo "<table width=\"100%\">\n";
                echo "<tr><td>\n";
                echo "    <fieldset class=\"cal_saisie $conf_nom\">\n";
                echo "    <legend class=\"boxlogin\">". _($conf_groupe) ."</legend>\n";
                $old_groupe = $conf_groupe ;
            }

            // si on est sur le parametre "lang" on liste les fichiers de langue du répertoire install/lang
            if($conf_nom=="lang")
            {
                echo "Choisissez votre langue :<br> \n";
                echo "Choose your language :<br>\n";
                // affichage de la liste des langues supportées ...
                // on lit le contenu du répertoire lang et on parse les nom de ficher (ex lang_fr_francais.php)
                //affiche_select_from_lang_directory("tab_new_values[$conf_nom]");
                affiche_select_from_lang_directory('lang', $conf_valeur);
            }
            else
            {
                // affichage commentaire
                echo "<br><i>". _($conf_commentaire) ."</i><br>\n";

                // affichage saisie variable
                if($conf_nom=="installed_version")
                {
                    echo "<b>$conf_nom&nbsp;&nbsp;=&nbsp;&nbsp;$conf_valeur</b><br>";
                }
                elseif( ($conf_type=="texte") || ($conf_type=="path") )
                {
                    echo "<b>$conf_nom</b>&nbsp;=&nbsp;<input type=\"text\" class=\"form-control\" size=\"50\" maxlength=\"200\" name=\"tab_new_values[$conf_nom]\" value=\"$conf_valeur\"><br>";
                }
                elseif($conf_type=="boolean")
                {
                    echo "<b>$conf_nom</b>&nbsp;=&nbsp;<select class=\"form-control\" name=\"tab_new_values[$conf_nom]\">";
                    echo "<option value=\"TRUE\"";
                    if($conf_valeur=="TRUE") echo "selected";
                    echo ">TRUE</option>";
                    echo "<option value=\"FALSE\"";
                    if($conf_valeur=="FALSE") echo "selected";
                    echo ">FALSE</option>";
                    echo "</select><br>";
                }
                elseif(substr($conf_type,0,4)=="enum")
                {
                    echo "<b>$conf_nom</b>&nbsp;=&nbsp;<select class=\"form-control\" name=\"tab_new_values[$conf_nom]\">";
                    $options=explode("/", substr(strstr($conf_type, '='),1));
                    for($i=0; $i<count($options); $i++)
                    {
                        echo "<option value=\"".$options[$i]."\"";
                        if($conf_valeur==$options[$i]) echo "selected";
                        echo ">".$options[$i]."</option>";
                    }
                    echo "</select><br>";
                }
                echo "<br>";
            }

        }

        echo "</td></tr>\n";
        echo "<tr><td align=\"right\">\n";
        echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
        echo "</td></tr>\n";


        /******************* GESTION DES PLUGINS V1.7 *************************/

        //rajout du formulaire de gestion des plugins : à partir de la version 1.7
        // - On détecte les plugins puis on propose de les installer
        // L'installation du plugin va lancer include/plugins/[nom_du_plugins]/plugin_install.php
        // plugin_install.php lance la création des tables supplémentaires;
        // normalement le format de nommage des tables est conges_plugin_[nom_du_plugin]. Exemple de table : conges_plugin_cet
        // il vaut mieux éviter de surcharger les tables existantes pour éviter les nombreux problèmes de compatibilité
        // lors d'un changement de version.
        // - Lorsqu'un plugin est installé, l'administrateur ou la personne autorisée pourra activer le plugin.
        // Le status qui s'affichera deviendra "activated"
        // Soit 4 statuts disponibles : not installed, installed, disable, activated
        // Correspondants à 4 fichiers dans le dossier du plugin : plugin_install.php, plugin_uninstall.php, plugin_active.php, plugin_inactive.php
        //Les statuts sont retrouvés par la table conges_plugins
        //Ensuite, les fichiers à inclure doivent être listés dans include/plugins/[nom_du_plugins]/allfilestoinclude.php
        // Ces fichiers à inclure contiennent le coeur de votre plugin.

        $my_plugins = scandir(PLUGINS_DIR);
        $plug_count = 0;
        echo "<table width=\"100%\">\n";
        echo "<tr><td>\n";
        echo "    <fieldset class=\"cal_saisie plugins\">\n";
        echo "    <legend class=\"boxlogin\">Plugins</legend>\n";
        foreach($my_plugins as $my_plugin){
            if(is_dir(PLUGINS_DIR."/$my_plugin") && !preg_match("/^\./",$my_plugin))
            {
                echo "Plugin détecté : ";
                echo "<b> $my_plugin </b>. This plugin is installed ? :
                    <select class=\"form-control\" name=tab_new_values[".$my_plugin."_installed]>";

                $sql_plug="SELECT p_is_active, p_is_install FROM conges_plugins WHERE p_name = '".$my_plugin."';";
                $ReqLog_plug = \includes\SQL::query($sql_plug);
                if($ReqLog_plug->num_rows !=0)
                {
                    while($plug = $ReqLog_plug->fetch_array()){
                        $p_install = $plug["p_is_install"];
                        if ($p_install == '1')
                        { echo "<option selected='selected' value='1'>Y</option><option value='0'>N</option>"; }
                        else
                        { echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>"; }
                        echo "</select>";
                        echo " ... Is activated ? : <select class=\"form-control\" name=tab_new_values[".$my_plugin."_activated]>";
                        $p_active = $plug["p_is_active"];
                        if ($p_active == '1')
                        { echo "<option selected='selected' value='1'>Y</option><option value='0'>N</option>"; }
                        else
                        { echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>"; }
                    }
                }
                else
                {
                    echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>";
                    echo "</select>";
                    echo " ... Is activated ? : <select class=\"form-control\" name=tab_new_values[".$my_plugin."_activated]>";
                    echo "<option value='1'>Y</option><option selected='selected' value='0'>N</option>";
                }
                echo "</select>";
                echo "<br />";
                $plug_count++;
            }
        }
        if($plug_count == 0){ echo "No plugin detected."; }
        echo "</td></tr>\n";
        echo "<tr><td align=\"right\">\n";
        echo "<input type=\"submit\" class=\"btn\"  value=\"". _('form_save_modif') ."\"><br>";
        echo "</td></tr>\n";
        /**********************************************************************/

        echo "</table>\n";
        echo "</form>\n";
    }

    /**
     * Encapsule le comportement du module d'affichage des logs
     *
     * @param string $session
     * @param bool   $DEBUG  Mode debug ?
     *
     * @return void
     * @access public
     * @static
     */
    public static function configurationModule($session, $DEBUG = false)
    {
        // verif des droits du user à afficher la page
        verif_droits_user($session, "is_admin", $DEBUG);

        if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


        /*** initialisation des variables ***/
        $action="";
        $tab_new_values=array();
        /************************************/

        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF=$_SERVER['PHP_SELF'];
        // GET / POST
        $action         = getpost_variable('action') ;
        $tab_new_values = getpost_variable('tab_new_values');

        /*************************************/

        if( $DEBUG ) { echo "tab_new_values = "; print_r($tab_new_values); echo "<br>\n"; }

        if($action=="commit")
            \config\Fonctions::commit_saisie($tab_new_values, $session, $DEBUG);
        else {
            echo "<div class=\"wrapper configure\">\n";
            \config\Fonctions::affichage_configuration($session, $DEBUG);
            echo "<div>\n";
        }
    }
}