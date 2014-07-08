# -*- coding: UTF-8 -*-


'''

ip_address = {
    'type' : 'uniquekey',
    'keys' : [ 'name' ],
    'modord' : [ 'del', 'add', 'set' ],
    'split_by' : ',',
    'split_keys' : [ 'policy' ] }
}

'type'      Menu type. May be: uniquekey, single, generic.
'keys'      List with key names to treat as uniqie key. May be 1 or more. This field is valid only for 'type' : 'uniquekey'
'modord'    Modification order. Possible values 'add', 'set', 'del'. This also works as possible actions that can be taken for given menu.
            If eg. 'del' is not specified, delete actions will be skipped.
'split'     Rules 'fields' may be split 'by' to make exact comparison. Eg. in /user/group policy value may be split by ','.
            This would result in: 'split' : { 'by' : ',', 'fields' : [ 'policy' ] }
'''

# Menu paths will inherit from DEFAULT
DEFAULT = { 'type':'uniquekey', 'modord':['set', 'add', 'del'], 'keys': ['name'], 'split_by':'', 'split_keys':[] }



# Menu paths that override default values.

MENU_PATHS = {
    '/interface/ethernet': { 'type':'uniquekey', 'modord':['set'] },

    '/port': { 'type':'uniquekey', 'modord':['set'] },

    '/routing/ospf/interface' : { 'type':'uniquekey', 'keys':['interface'] },
    '/routing/ospf/network' : { 'type':'uniquekey', 'keys':['network'] },

    '/user' : { 'type':'uniquekey', 'modord':['add', 'set', 'del'] },
    '/user/group' : { 'type':'uniquekey', 'modord':['add', 'set', 'del'], 'split_by':',', 'split_keys':['policy'] },

    '/ip/firewall/service-port' : { 'type':'uniquekey', 'modord':['set'] },
    '/ip/hotspot/service-port' : { 'type':'uniquekey', 'modord':['set'] },
    '/ip/neighbour/discovery' : { 'type':'uniquekey', 'modord':['set'] },
    '/ip/service' : { 'type':'uniquekey', 'modord':['set'] },
    '/ip/dhcp-client' : {'type':'uniquekey', 'keys':['interface'] },
    '/ip/dhcp-server/alert' : {'type':'uniquekey', 'keys':['interface'] },
    '/ip/dhcp-server/lease' : {'type':'uniquekey', 'keys':['address'] },
    '/ip/dhcp-server/network' : {'type':'uniquekey', 'keys':['address'] },
    '/ip/upnp/interfaces' : { 'type':'uniquekey', 'keys':['interface'] },

    '/queue/interface' : { 'type':'uniquekey', 'keys':['interface'], 'modord':['set'] },

    '/ppp/profile' : { 'type':'uniquekey', 'keys':['interface'] },
    '/ppp/active' : { 'type':'uniquekey', 'modord':['del'] },

    '/tool/mac-server' : { 'type':'uniquekey', 'keys':['interface'] },
    '/tool/mac-server/mac-winbox' : { 'type':'uniquekey', 'keys':['interface'] }
    }




#  Menu paths to check for keys, modord, menutype
# {'/caps-man/aaa',
#  '/caps-man/manager',
#  '/interface/bridge',
#  '/interface/bridge/port',
#  '/interface/bridge/settings',
#  '/interface/eoip',
#  '/interface/ethernet',
#  '/interface/gre',
#  '/interface/ipip',
#  '/interface/l2tp-server/server',
#  '/interface/ovpn-server/server',
#  '/interface/pptp-server',
#  '/interface/pptp-server/server',
#  '/interface/sstp-server/server',
#  '/interface/vlan',
#  '/interface/wireless/align',
#  '/interface/wireless/cap',
#  '/interface/wireless/security-profiles',
#  '/interface/wireless/sniffer',
#  '/interface/wireless/snooper',
#  '/ip/accounting',
#  '/ip/accounting/web-access',
#  '/ip/address',
#  '/ip/dhcp-client',
#  '/ip/dhcp-client/option',
#  '/ip/dhcp-server',
#  '/ip/dhcp-server/config',
#  '/ip/dhcp-server/lease',
#  '/ip/dhcp-server/network',
#  '/ip/dns',
#  '/ip/firewall/address-list',
#  '/ip/firewall/connection/tracking',
#  '/ip/firewall/filter',
#  '/ip/firewall/nat',
#  '/ip/firewall/service-port',
#  '/ip/hotspot/profile',
#  '/ip/hotspot/service-port',
#  '/ip/hotspot/user/profile',
#  '/ip/ipsec/mode-cfg',
#  '/ip/ipsec/mode-config',
#  '/ip/ipsec/policy/group',
#  '/ip/ipsec/proposal',
#  '/ip/neighbor/discovery',
#  '/ip/neighbor/discovery/settings',
#  '/ip/pool',
#  '/ip/proxy',
#  '/ip/route',
#  '/ip/service',
#  '/ip/settings',
#  '/ip/smb',
#  '/ip/smb/shares',
#  '/ip/smb/users',
#  '/ip/socks',
#  '/ip/ssh',
#  '/ip/traffic-flow',
#  '/ip/upnp',
#  '/ipv6/address',
#  '/ipv6/nd',
#  '/ipv6/nd/prefix/default',
#  '/ipv6/route',
#  '/ipv6/settings',
#  '/lcd',
#  '/lcd/interface',
#  '/lcd/interface/pages',
#  '/lcd/pin',
#  '/lcd/screen',
#  '/mpls',
#  '/mpls/interface',
#  '/mpls/ldp',
#  '/port',
#  '/port/firmware',
#  '/ppp/aaa',
#  '/ppp/profile',
#  '/ppp/secret',
#  '/queue/interface',
#  '/queue/simple',
#  '/queue/type',
#  '/radius/incoming',
#  '/routing/bfd/interface',
#  '/routing/bgp/aggregate',
#  '/routing/bgp/instance',
#  '/routing/bgp/network',
#  '/routing/bgp/peer',
#  '/routing/filter',
#  '/routing/igmp-proxy',
#  '/routing/mme',
#  '/routing/ospf-v3/area',
#  '/routing/ospf-v3/instance',
#  '/routing/ospf/area',
#  '/routing/ospf/instance',
#  '/routing/ospf/interface',
#  '/routing/ospf/network',
#  '/routing/pim',
#  '/routing/rip',
#  '/routing/ripng',
#  '/snmp',
#  '/snmp/community',
#  '/system/clock',
#  '/system/clock/manual',
#  '/system/console',
#  '/system/console/screen',
#  '/system/gps',
#  '/system/hardware',
#  '/system/health',
#  '/system/identity',
#  '/system/lcd',
#  '/system/lcd/page',
#  '/system/logging',
#  '/system/logging/action',
#  '/system/note',
#  '/system/ntp/client',
#  '/system/ntp/server',
#  '/system/resource/irq',
#  '/system/resource/irq/rps',
#  '/system/routerboard/settings',
#  '/system/scheduler',
#  '/system/script',
#  '/system/upgrade/mirror',
#  '/system/watchdog',
#  '/tool/bandwidth-server',
#  '/tool/e-mail',
#  '/tool/graphing',
#  '/tool/graphing/interface',
#  '/tool/graphing/queue',
#  '/tool/graphing/resource',
#  '/tool/mac-server',
#  '/tool/mac-server/mac-winbox',
#  '/tool/mac-server/ping',
#  '/tool/sms',
#  '/tool/sniffer',
#  '/tool/traffic-generator',
#  '/tool/user-manager/customer',
#  '/user/aaa',
#  '/user/group'/}
#