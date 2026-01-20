<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Force navbar to display exactly the same on mobile as desktop */
/* Hide the hamburger toggle button on all screen sizes */
.navbar-toggle {
   display: none !important;
}

/* Force navbar collapse to always be visible and not collapse */
.navbar-collapse {
   display: flex !important;
   height: auto !important;
   padding-bottom: 0 !important;
   overflow: visible !important;
   border-top: 0 !important;
}

/* Ensure navbar items stay inline on all screen sizes */
.navbar-collapse.collapse {
   display: flex !important;
}

/* Keep navbar-right aligned to the right on all screen sizes */
.navbar-nav.navbar-right {
   float: right !important;
   margin: 0 !important;
}

/* Prevent navbar items from stacking vertically on mobile */
@media (max-width: 767px) {
   .navbar-nav {
      float: right !important;
      margin: 0 !important;
   }
   
   .navbar-nav > li {
      float: left !important;
      display: inline-block !important;
   }
   
   .navbar-nav > li > a {
      padding-top: 15px !important;
      padding-bottom: 15px !important;
      padding-left: 5px !important;
      padding-right: 5px !important;
   }
   
   /* Keep dropdowns positioned correctly */
   .navbar-nav .dropdown-menu {
      position: absolute !important;
   }
   
   /* Ensure the navbar header and collapse stay on the same line */
   .navbar-header {
      float: left !important;
   }
   
   .navbar-collapse {
      border-top: 0 !important;
      box-shadow: none !important;
      width: auto !important;
      margin: 0 !important;
   }
   
   /* Prevent text from wrapping */
   .navbar-nav > li > a {
      white-space: nowrap !important;
   }
}

/* Ensure container doesn't force wrapping */
.navbar > .container {
   display: flex !important;
   flex-wrap: nowrap !important;
   justify-content: space-between !important;
   align-items: center !important;
}

/* Ensure navbar-header (logo) stays on extreme left */
.navbar-header {
   margin-left: 0 !important;
   margin-right: auto !important;
}

/* Ensure navbar-collapse (nav items) stays on extreme right */
.navbar-collapse {
   margin-left: auto !important;
   margin-right: 0 !important;
   flex-grow: 0 !important;
   flex-shrink: 0 !important;
}
</style>
<nav class="navbar navbar-default header">
   <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
         <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#theme-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
         </button>
         <?php get_company_logo('purchase/vendors_portal','navbar-brand logo'); ?>
      </div>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="theme-navbar-collapse">
         <ul class="nav navbar-nav navbar-right">
          <?php  if (is_vendor_logged_in()) { ?>
               <?php /*
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/items'); ?>">
                     <?php
                     
                     echo _l('items');
                    ?>
                  </a>
               </li>
               
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/purchase_request'); ?>">
                     <?php
                     
                     echo _l('purchase_request');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/quotations'); ?>">
                     <?php
                     
                     echo _l('quotations');
                    ?>
                  </a>
               </li>
                <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/purchase_order'); ?>">
                     <?php
                     
                     echo _l('purchase_order');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/contracts'); ?>">
                     <?php
                     
                     echo _l('contracts');
                    ?>
                  </a>
               </li>
               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/invoices'); ?>">
                     <?php
                     
                     echo _l('pur_invoices');
                    ?>
                  </a>
               </li>

               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/order_returns'); ?>">
                     <?php
                     
                     echo _l('pur_order_returns');
                    ?>
                  </a>
               </li>
               */ ?> 

               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/invoices'); ?>">
                     <?php
                     
                     echo _l('pur_invoices');
                    ?>
                  </a>
               </li>

               <li class="customers-nav-item-items">
                  <a href="<?php echo site_url('purchase/vendors_portal/production'); ?>">
                     <?php
                     
                     echo _l('production');
                    ?>
                  </a>
               </li>

        <?php } ?>
        
          
            <?php if(is_vendor_logged_in()) { ?>
               <li class="dropdown customers-nav-item-profile">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                     <img src="<?php echo vendor_contact_profile_image_url($contact->id,'thumb'); ?>" data-toggle="tooltip" data-title="<?php echo html_escape($contact->firstname . ' ' .$contact->lastname); ?>" data-placement="bottom" class="client-profile-image-small mright5">
                     <span class="caret"></span>
                     </a>
                     <ul class="dropdown-menu animated fadeIn">
                        <?php /*
                        <li class="customers-nav-item-edit-profile">
                           <a href="<?php echo site_url('purchase/vendors_portal/profile'); ?>">
                              <?php echo _l('clients_nav_profile'); ?>
                           </a>
                        </li>
                        <?php if($contact->is_primary == 1){ ?>
                           <li class="customers-nav-item-company-info">
                              <a href="<?php echo site_url('purchase/vendors_portal/company'); ?>">
                                 <?php echo _l('client_company_info'); ?>
                              </a>
                           </li>
                        <?php } ?>
                    
                        
                     <li class="customers-nav-item-languages">
                        <a href="<?php echo site_url('purchase/vendors_portal/setting_language'); ?>">
                             <?php echo _l('language'); ?>
                        </a>
                     </li> 
                     */ ?>

                     <li class="customers-nav-item-logout">
                        <a href="<?php echo site_url('purchase/authentication_vendor/logout'); ?>">
                           <?php echo _l('clients_nav_logout'); ?>
                        </a>
                     </li>
                  </ul>
               </li>
            <?php } ?>
            
         </ul>
      </div>
      <!-- /.navbar-collapse -->
   </div>
   <!-- /.container-fluid -->
</nav>
