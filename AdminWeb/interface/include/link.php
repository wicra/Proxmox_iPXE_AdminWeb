
<!-- LIEN DE REDIRECTION CENTRALISE  -->
<?php
    /////////////////////////////////////////////////////////
    //                   LIEN POUR DEV                     //
    /////////////////////////////////////////////////////////
    $file_path_conf = '../../../dhcp/dhcpd_hosts.conf';
    $file_path_admin = '../../dhcp/dhcpd_hosts.conf';
    $file_path_trie = '../../dhcp/dhcpd_hosts.conf';
    $LEASES_FILE = "../../dhcp/dhcpd.leases";
    $DHCP_CONF = "../../dhcp/dhcpd_hosts.conf";
    $config_file_dhcp = '../../dhcp/dhcpd_range.conf';
    $config_file_auto = 'include/range_ip_fixe.php';
    $configFileUnknown = "../../dhcp/add_boot_ipxe_unknown.conf";
    $answer_toml = "../../conf_proxmox/answer.toml"

    /////////////////////////////////////////////////////////
    //            LIEN DEPLOIEMENT VERS SERVER             //
    /////////////////////////////////////////////////////////
    // $file_path_conf = "/etc/dhcp/dhcpd_hosts.conf";
    // $file_path_admin = "/etc/dhcp/dhcpd_hosts.conf";
    // $file_path_trie = '/etc/dhcp/dhcpd_hosts.conf';
    // $LEASES_FILE = "/var/lib/dhcp/dhcpd.leases";
    // $DHCP_CONF = "/etc/dhcp/dhcpd_hosts.conf";
    // $config_file_dhcp = "/etc/dhcp/dhcpd_range.conf";
    // $config_file_auto = "include/range_ip_fixe.php";
    // $configFileUnknown = "/etc/dhcp/add_boot_ipxe_unknown.conf";
    // $answer_toml = "/var/www/html/proxmox/answer.toml"
?>