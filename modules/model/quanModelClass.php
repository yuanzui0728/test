<?php
 goto uHjoY; a8aLf: exit("\101\x63\143\145\163\x73\40\104\x65\156\x69\x65\144"); goto xFvB4; uHjoY: if (defined("\111\x4e\x5f\111\x41")) { goto sSZly; } goto a8aLf; xFvB4: sSZly: goto bjCYb; bjCYb: class Quan_SnailFishShopModel { public function get_memberid() { goto RpDPE; NzOXh: global $_GPC; goto tHCYe; r9aL5: return false; goto XElpf; tHCYe: $token = $_GPC["\164\157\153\145\x6e"]; goto WpG3h; P02qm: $weprogram_token = pdo_fetch("\163\x65\154\x65\143\164\40\155\145\155\x62\145\x72\137\151\144\x20\146\x72\x6f\x6d\40" . tablename("\x6c\151\x6f\156\x66\x69\x73\150\x5f\x63\157\x6d\x73\150\x6f\160\x5f\167\x65\160\x72\x6f\147\x72\x61\155\137\164\157\x6b\145\x6e") . "\40\167\150\x65\x72\x65\x20\x75\x6e\151\x61\143\x69\x64\x3d\x3a\165\156\x69\x61\x63\x69\144\40\x61\x6e\x64\x20\x74\157\x6b\145\156\x3d\72\x74\157\153\145\156\40", $token_param); goto MKjiM; MKjiM: if (!(empty($weprogram_token) || empty($weprogram_token["\155\145\155\142\x65\x72\x5f\x69\144"]))) { goto fvdYj; } goto r9aL5; WpG3h: $token_param = array(); goto Zg6Xn; RpDPE: global $_W; goto NzOXh; lR6xT: return $member_id = $weprogram_token["\155\145\x6d\x62\145\x72\137\151\144"]; goto CXDN8; FFRXl: fvdYj: goto lR6xT; yg6uY: $token_param["\x3a\x74\157\153\x65\156"] = $token; goto P02qm; XElpf: die; goto FFRXl; Zg6Xn: $token_param["\72\x75\x6e\x69\141\143\x69\x64"] = $_W["\x75\x6e\151\141\143\151\144"]; goto yg6uY; CXDN8: } public function check_quan($seller_id) { goto PQ6Zj; lKLy4: xmV0O: goto IVYpL; sMNJS: if (empty($group_info)) { goto ltjqo; } goto qPAZW; qPAZW: $group_id = $group_info["\151\x64"]; goto YMvt4; svYfC: pdo_insert("\x6c\x69\x6f\156\146\151\163\x68\x5f\x63\x6f\155\x73\150\x6f\160\x5f\x67\162\157\165\x70", $ins_data); goto NB_J3; hnGXg: $ins_data["\x6d\145\155\x62\145\162\x5f\x63\x6f\165\x6e\x74"] = 0; goto cYHm8; KvbQD: ltjqo: goto xnQ10; pe42X: global $_GPC; goto hH1No; xnQ10: $ins_data = array(); goto x3V96; cYHm8: $ins_data["\143\x72\x65\141\x74\145\137\x74\x69\x6d\145"] = time(); goto svYfC; W5UhY: $ins_data["\x70\157\x73\x74\x5f\143\x6f\165\156\x74"] = 0; goto m1yeF; PQ6Zj: global $_W; goto pe42X; x3V96: $ins_data["\x73\x65\x6c\x6c\145\162\137\x69\144"] = $seller_id; goto BUoh2; dxole: $ins_data["\164\x69\164\x6c\145"] = ''; goto W5UhY; YMvt4: goto xmV0O; goto KvbQD; hH1No: $group_info = pdo_fetch("\x73\x65\x6c\x65\143\164\40\x2a\40\146\x72\x6f\155\x20" . tablename("\154\x69\157\x6e\x66\x69\163\150\x5f\143\x6f\x6d\163\150\157\160\137\x67\162\157\x75\x70") . "\x20\x77\x68\145\x72\x65\40\163\145\x6c\154\x65\162\137\151\144\75\x3a\x73\145\154\154\x65\162\x5f\x69\x64\x20\141\x6e\144\40\165\156\151\x61\143\151\144\x3d\72\165\x6e\151\x61\143\x69\x64\x20", array("\72\163\145\154\x6c\x65\x72\x5f\151\x64" => $seller_id, "\72\165\156\151\x61\x63\x69\x64" => $_W["\165\156\x69\141\143\151\x64"])); goto sMNJS; m1yeF: $ins_data["\163\x74\x61\164\165\x73"] = 1; goto hnGXg; BUoh2: $ins_data["\x75\x6e\x69\141\143\151\x64"] = $_W["\165\x6e\x69\x61\143\x69\x64"]; goto dxole; NB_J3: $group_id = pdo_insertid(); goto lKLy4; IVYpL: return $group_id; goto wzxNF; wzxNF: } public function member_add_group($member_id, $group_id) { goto NGsI2; OspNV: $group_member_data["\x67\162\x6f\x75\x70\x5f\151\x64"] = $group_id; goto rvph8; BVQsK: $group_member_data["\163\164\141\164\165\x73"] = 1; goto p19Th; rvph8: $group_member_data["\155\x65\155\142\145\162\x5f\151\x64"] = $member_id; goto BVQsK; unvx9: $group_member_data["\143\162\x65\141\164\145\x5f\164\x69\x6d\145"] = time(); goto jg6nT; p19Th: $group_member_data["\x6c\x61\x73\x74\137\x76\x69\145\167"] = time(); goto lpC91; NGsI2: $group_member_data = array(); goto OspNV; lpC91: $group_member_data["\160\157\x73\151\x74\x69\x6f\x6e"] = 1; goto unvx9; jg6nT: pdo_insert("\154\x69\157\156\146\151\x73\x68\137\x63\157\155\163\150\157\160\x5f\147\x72\x6f\x75\160\x5f\155\x65\x6d\142\145\x72", $group_member_data); goto FATYj; FATYj: } public function member_fav_post($member_id, $post_id) { goto x4wDl; yXhFS: return array("\143\x6f\x64\145" => 1, "\146\141\166\x5f\151\144" => $rs); goto V51oF; J7LP9: pdo_update("\x6c\151\157\156\x66\151\163\x68\x5f\143\157\x6d\x73\x68\157\160\x5f\147\162\157\x75\x70\x5f\160\157\x73\x74", array("\146\141\166\137\143\x6f\x75\156\164\40\55\x3d" => 1), array("\x69\x64" => $post_id)); goto c0kvf; dzFAk: $rs = pdo_insertid(); goto Zz3qh; nr8bA: $fav_data["\155\145\x6d\142\x65\162\137\x69\x64"] = $member_id; goto wY1Zj; mcgIu: $fav_data["\146\141\x76\137\164\x69\155\x65"] = time(); goto rYAQ7; ZL2ql: goto fJHBa; goto a3Y0Y; rYAQ7: pdo_insert("\x6c\151\157\x6e\146\x69\x73\x68\137\143\x6f\155\x73\150\x6f\x70\137\x67\162\x6f\x75\160\x5f\160\157\x73\164\137\146\141\166", $fav_data); goto dzFAk; umXx3: $fav_info = pdo_fetch("\x73\x65\x6c\145\143\x74\x20\x2a\40\146\162\157\155\x20" . tablename("\x6c\151\x6f\x6e\x66\x69\163\150\x5f\143\157\155\163\150\x6f\160\137\147\162\x6f\165\x70\137\x70\157\x73\x74\x5f\x66\x61\166") . "\40\167\x68\x65\162\x65\40\163\145\154\154\x65\162\x5f\x69\144\x3d\72\x73\x65\x6c\x6c\145\162\x5f\x69\144\x20\141\156\x64\x20\x70\x6f\x73\164\x5f\x69\144\75\x3a\x70\157\x73\x74\x5f\151\144", array("\72\155\145\155\142\145\162\x5f\x69\x64" => $member_id, "\x3a\x70\157\163\164\137\x69\x64" => $post_id)); goto trrh3; p74jx: pdo_update("\154\151\x6f\x6e\x66\x69\163\150\137\143\x6f\155\x73\150\157\160\x5f\x67\x72\x6f\x75\x70\x5f\x70\x6f\163\164", array("\x66\x61\x76\137\143\x6f\165\x6e\164\40\53\x3d" => 1), array("\151\x64" => $post_id)); goto P5tm5; P5tm5: dXzH0: goto yXhFS; trrh3: $fav_id = $fav_info["\x69\x64"]; goto oK7hg; oK7hg: pdo_delete("\x6c\x69\x6f\x6e\146\151\x73\x68\137\x63\x6f\155\x73\x68\157\x70\137\147\162\x6f\165\x70\x5f\x70\157\163\x74\137\146\141\166", array("\x6d\145\x6d\142\145\162\137\151\x64" => $member_id, "\160\157\x73\x74\137\151\x64" => $post_id)); goto J7LP9; Zz3qh: if (!$rs) { goto dXzH0; } goto p74jx; Cvqe4: if (empty($post_fav)) { goto WZNf1; } goto umXx3; a3Y0Y: WZNf1: goto ugoSB; wY1Zj: $fav_data["\160\157\x73\x74\137\x69\x64"] = $post_id; goto mcgIu; ugoSB: $fav_data = array(); goto nr8bA; V51oF: fJHBa: goto iwDOg; x4wDl: $post_fav = pdo_fetch("\x73\x65\154\x65\143\164\40\x2a\x20\x66\162\157\x6d\x20" . tablename("\x6c\151\x6f\x6e\146\151\x73\150\x5f\143\157\155\x73\150\157\160\137\x67\162\157\165\x70\137\x70\x6f\163\x74\137\x66\141\166") . "\x20\x77\150\145\162\x65\40\x6d\x65\x6d\142\x65\x72\x5f\x69\144\75\72\155\145\155\142\145\162\137\x69\x64\40\x61\156\144\x20\x70\157\x73\164\137\151\144\75\x3a\x70\x6f\x73\x74\x5f\x69\144", array("\72\155\x65\x6d\x62\145\x72\x5f\x69\x64" => $member_id, "\x3a\x70\x6f\x73\x74\137\x69\144" => $post_id)); goto Cvqe4; c0kvf: return array("\143\157\x64\x65" => 2, "\x66\141\166\137\x69\144" => $fav_id); goto ZL2ql; iwDOg: } public function comment_group_post($post_id, $content, $member_id, $to_member_id) { goto ogn97; XHpxR: $reply_data["\x63\157\x6e\164\x65\156\164"] = htmlspecialchars($content); goto qaiiR; NMuRP: if (!$rs) { goto QnQbe; } goto tVADj; FTzYC: return $rs; goto BtEJZ; bWGr0: $rs = M("\x67\162\157\x75\160\x5f\154\x7a\154\x5f\x72\x65\160\154\171")->add($reply_data); goto NMuRP; JbXwo: pdo_update("\154\x69\x6f\x6e\x66\x69\163\150\137\143\157\155\x73\150\x6f\x70\137\147\x72\157\165\160\137\x70\x6f\163\x74", array("\154\x61\x73\164\x5f\162\145\160\x6c\x79\x5f\x74\x69\x6d\x65" => time()), array("\x69\x64" => $post_id)); goto XGGpV; Bz6ei: $reply_data["\163\x74\141\x74\165\x73"] = 1; goto tNqe6; Hzd0x: $reply_data["\x74\157\x5f\x6d\x65\155\142\x65\162\137\151\144"] = $to_member_id; goto Bz6ei; tNqe6: $reply_data["\x63\162\145\141\164\145\x5f\x74\x69\x6d\145"] = time(); goto bWGr0; ogn97: $reply_data = array(); goto QPqJ1; qaiiR: $reply_data["\155\145\x6d\142\145\x72\137\151\144"] = $member_id; goto Hzd0x; QPqJ1: $reply_data["\x70\x6f\163\164\x5f\x69\x64"] = $post_id; goto XHpxR; tVADj: pdo_update("\x6c\x69\x6f\156\146\x69\x73\x68\137\x63\157\155\163\150\x6f\160\x5f\147\x72\157\x75\x70\137\x70\157\x73\164", array("\162\145\160\x6c\171\137\143\157\165\156\164\40\x2d\75" => 1), array("\x69\x64" => $post_id)); goto JbXwo; XGGpV: QnQbe: goto FTzYC; BtEJZ: } public function send_group_post($data) { goto H4ggh; SPWjg: $ins_data["\x69\x73\137\x73\150\141\x72\x65"] = isset($data["\x69\163\x5f\x73\x68\141\x72\145"]) ? $data["\x69\x73\137\x73\150\141\162\x65"] : 0; goto NWnel; jH5Ye: $ins_data["\x67\x72\157\x75\x70\x5f\x69\144"] = $data["\x67\x72\157\165\x70\137\151\144"]; goto YPhaf; UIIQG: $ins_data["\143\x6f\x6e\164\x65\x6e\x74"] = $data["\143\157\x6e\164\145\156\164"]; goto QiScp; dVFKS: $ins_data["\165\156\151\141\x63\x69\x64"] = $data["\165\156\151\x61\x63\151\144"]; goto jH5Ye; QiScp: $ins_data["\154\x69\x6e\153"] = isset($data["\154\x69\156\153"]) ? $data["\154\x69\x6e\x6b"] : ''; goto SPWjg; Bv7bA: pdo_insert("\154\151\x6f\156\146\151\x73\150\x5f\x63\157\x6d\163\x68\x6f\160\x5f\x67\162\x6f\x75\x70\x5f\160\157\163\164", $ins_data); goto VLKO6; SHkrz: $ins_data["\143\162\145\x61\164\x65\x5f\x74\151\155\145"] = time(); goto Bv7bA; NWnel: $ins_data["\x73\164\x61\x74\x75\163"] = 1; goto Ibpsy; VLKO6: $res = pdo_insertid(); goto hXk9N; hXk9N: return $res; goto mWDB3; g1A_Q: $ins_data["\x61\166\x61\x74\141\x72"] = isset($data["\141\x76\141\164\x61\162"]) ? $data["\141\x76\x61\x74\x61\x72"] : ''; goto bUBLQ; NYEaC: $ins_data["\146\141\166\x5f\143\x6f\x75\x6e\x74"] = 0; goto EeVQZ; Oij3E: $ins_data["\x6d\145\155\x62\145\162\x5f\x69\144"] = $data["\x6d\145\x6d\x62\145\162\x5f\x69\144"]; goto dVFKS; Ibpsy: $ins_data["\151\x73\x5f\x76\151\x72"] = isset($data["\x69\163\x5f\x76\x69\162"]) ? $data["\151\163\x5f\x76\151\x72"] : 0; goto g1A_Q; YPhaf: $ins_data["\147\x6f\x6f\x64\x73\x5f\x69\x64"] = $data["\147\x6f\157\x64\x73\137\x69\144"]; goto oYsV2; oYsV2: $ins_data["\164\151\164\x6c\x65"] = $data["\164\x69\164\154\145"]; goto UIIQG; bUBLQ: $ins_data["\165\163\x65\162\x5f\156\141\x6d\x65"] = isset($data["\165\163\x65\x72\137\x6e\x61\155\145"]) ? $data["\x75\163\145\x72\x5f\156\x61\x6d\145"] : ''; goto C6qJI; EeVQZ: $ins_data["\162\145\x70\x6c\171\x5f\143\x6f\x75\156\164"] = 0; goto SHkrz; C6qJI: $ins_data["\x6c\141\x73\164\x5f\x72\x65\x70\x6c\x79\137\x74\151\x6d\x65"] = time(); goto NYEaC; H4ggh: $ins_data = array(); goto Oij3E; mWDB3: } public function load_group_post($group_id, $post_id, $up_down, $limit = 10) { goto g6sKe; WJZxp: $list = pdo_fetchall($sql); goto WyAup; m0aOE: DoKSJ: goto ML9ZO; ydhoR: global $_GPC; goto S7Dqa; QA6CG: $order_by = "\x20\x70\x2e\x69\x64\40\x64\145\x73\143\40"; goto lL7Uk; JA90U: QWmKX: goto f90rg; Yzdzf: $sql = "\163\145\x6c\x65\143\x74\40\x70\x2e\x2a\54\155\56\x75\x73\x65\162\x6e\141\x6d\x65\x2c\155\56\141\x76\x61\164\x61\162\40\x61\163\x20\x61\166\x61\x74\141\x72\x32\x20\x66\x72\x6f\155\40" . tablename("\x6c\151\x6f\x6e\146\151\163\x68\137\143\157\155\x73\x68\x6f\x70\137\147\162\x6f\165\160\x5f\x70\x6f\x73\164") . "\40\141\163\x20\x70\40\x6c\x65\146\164\x20\152\x6f\151\156\x20" . tablename("\163\x6e\141\x69\154\x66\x69\x73\150\x5f\155\x65\x6d\142\x65\x72") . "\x20\x61\163\40\155\x20\x6f\x6e\40\160\x2e\155\x65\155\x62\x65\x72\x5f\x69\144\x20\75\40\x6d\x2e\x6d\x65\x6d\142\145\x72\x5f\151\x64\x20\x77\150\145\162\145\40\61\75\x31\x20\40{$where}\x20\157\162\144\145\x72\40\x62\171\40{$order_by}\40\x6c\x69\155\x69\164\x20{$limit}\40"; goto WJZxp; S7Dqa: $where = "\x20\x61\x6e\144\x20\x70\x2e\147\162\x6f\165\x70\137\x69\x64\x20\x3d\40{$group_id}\x20"; goto Zn4Jc; f90rg: $order_by = "\40\160\56\x69\144\40\144\x65\163\x63\40"; goto DBcZG; g6sKe: global $_W; goto ydhoR; zDIcp: if (!($post_id > 0)) { goto QWmKX; } goto nH2kY; ZFwZV: if ($up_down == 1) { goto bCde8; } goto w29Dj; lL7Uk: goto MXfHz; goto aEAxr; aEAxr: bCde8: goto zDIcp; nH2kY: $where .= "\x20\x61\x6e\144\x20\x70\x2e\x69\144\x20\74{$post_id}\40"; goto JA90U; DBcZG: MXfHz: goto Yzdzf; Zn4Jc: $order_by = ''; goto ZFwZV; w29Dj: $where .= "\x20\141\x6e\144\40\x70\56\x69\x64\x20\76{$post_id}\40"; goto QA6CG; ML9ZO: return $list; goto GeSMM; WyAup: foreach ($list as $key => $val) { goto VvZ6l; Zx2r1: if ($val["\x69\x73\x5f\x76\151\162"] == 1) { goto w4qny; } goto M5fgF; G2UpS: $val["\x68\x61\x73\x5f\146\141\x76"] = empty($has_fav) ? false : true; goto FPdvx; vSRAq: $val["\154\141\x73\164\137\162\x65\x70\154\171\x5f\x64\141\164\145"] = date("\x59\x2d\155\x2d\144\40\110\x3a\151\x3a\163", $val["\154\x61\x73\164\137\x72\x65\x70\154\171\137\164\x69\x6d\145"]); goto WC_qX; v_z_E: $val["\x63\x6f\x6e\164\145\x6e\164"] = $image_list[0] != '' ? $image_list : ''; goto snDfO; KY2z7: $val["\165\156\141\155\x65"] = $val["\x75\x73\145\x72\x5f\x6e\141\155\145"]; goto H_n57; W7f2f: RIIAk: goto vSRAq; UtPZ9: $val["\x61\x76\141\164\141\162"] = tomedia($val["\141\x76\x61\164\x61\x72"]); goto ulHXQ; HWV4B: $where = "\167\150\145\162\145\40\x69\x64\75" . $val["\147\x6f\157\144\163\137\151\x64"]; goto Mdwj7; g33Rv: $val["\147\x6f\x6f\x64\163\x5f\151\x6e\146\157"]["\x62\x75\171\137\165\163\x65\x72"] = $this->get_goods_pin_avatar($val["\x67\x6f\x6f\x64\x73\137\x69\144"], 5); goto W7f2f; WC_qX: $list[$key] = $val; goto P6T7I; qEhUM: if (!($val["\x67\157\157\144\163\x5f\x69\144"] != 0)) { goto RIIAk; } goto HWV4B; ulHXQ: trivq: goto D2XOf; Y1Ye3: goto trivq; goto E4_wR; VvZ6l: $has_fav = pdo_fetch("\163\x65\154\x65\x63\x74\40\x2a\x20\146\x72\x6f\x6d\40" . tablename("\154\151\157\156\146\x69\163\150\x5f\x63\157\x6d\x73\x68\157\160\x5f\147\x72\157\x75\160\137\160\x6f\x73\164\137\146\x61\166") . "\x20\167\x68\x65\x72\x65\x20\x75\x6e\151\141\x63\x69\x64\75\72\165\156\151\141\x63\x69\x64\40\x61\x6e\144\40\x70\x6f\x73\164\137\151\x64\75\72\x70\x6f\x73\164\137\151\x64", array("\x3a\x75\156\x69\x61\143\x69\x64" => $_W["\165\x6e\x69\x61\x63\151\x64"], "\x3a\x70\x6f\x73\164\137\x69\144" => $val["\151\144"])); goto Zx2r1; ZVHXL: Dynea: goto v_z_E; qPNTz: $image_list = explode("\x2c", $val["\x63\x6f\156\x74\145\x6e\164"]); goto vZ9eX; P6T7I: wRyOJ: goto qcxFY; vZ9eX: foreach ($image_list as $kkk => $vvv) { goto w0tF5; g_hmf: $image_list[$kkk] = $vvv; goto rJJHz; w0tF5: $vvv = tomedia($vvv); goto g_hmf; rJJHz: MENgX: goto evo1Q; evo1Q: } goto ZVHXL; snDfO: $val["\146\x61\166\x5f\154\151\163\164"] = $this->get_post_fav_info($val["\151\x64"]); goto SXvFm; SXvFm: $val["\143\157\155\155\145\x6e\x74\137\x6c\151\x73\164"] = $this->get_post_comment_info($val["\x69\x64"]); goto qEhUM; M5fgF: $val["\141\166\141\x74\x61\162"] = $val["\141\x76\x61\164\141\162\62"]; goto Y1Ye3; E4_wR: w4qny: goto KY2z7; H_n57: $val["\x75\x73\x65\162\156\x61\x6d\145"] = $val["\165\x73\145\162\137\x6e\141\x6d\145"]; goto UtPZ9; Gcgb_: $val["\143\157\156\164\145\x6e\164"] = unserialize($val["\x63\x6f\156\x74\145\156\164"]); goto qPNTz; Mdwj7: $val["\147\x6f\x6f\144\163\137\151\156\x66\157"] = $this->get_goods_info($where); goto g33Rv; FPdvx: $val["\164\151\x74\x6c\145"] = htmlspecialchars_decode($val["\164\151\164\x6c\145"]); goto Gcgb_; D2XOf: $val["\x75\x6e\141\155\145"] = $val["\165\163\145\x72\156\x61\x6d\x65"]; goto G2UpS; qcxFY: } goto m0aOE; GeSMM: } public function get_goods_pin_avatar($goods_id, $limit = 5) { goto QUg2n; QUg2n: $sql = "\x73\x65\x6c\x65\143\x74\x20\x64\151\x73\x74\x69\x6e\143\x74\50\155\56\155\x65\155\142\145\162\137\151\x64\51\x2c\x20\x6d\x2e\x61\x76\x61\164\141\x72\x20\x66\x72\157\x6d\x20" . tablename("\154\x69\157\x6e\146\x69\163\150\137\143\x6f\155\163\x68\x6f\x70\x5f\x6f\162\144\x65\x72\137\x67\x6f\x6f\144\163") . "\40\141\x73\x20\x6f\147\x20\54" . tablename("\163\156\x61\x69\x6c\x66\x69\163\x68\x5f\157\x72\x64\145\x72") . "\40\141\163\x20\x6f\54" . tablename("\x73\156\141\151\x6c\146\x69\163\x68\x5f\155\145\x6d\x62\145\162") . "\x20\x61\163\40\x6d\x20\167\150\x65\162\145\40\157\147\x2e\x6f\162\144\145\x72\x5f\x69\144\x3d\x6f\56\157\162\144\145\x72\x5f\151\144\40\141\156\144\40\x6f\56\155\145\155\x62\x65\162\x5f\x69\x64\x3d\x6d\x2e\x6d\145\155\x62\x65\x72\137\x69\144\40\40\x61\156\x64\40\x6f\147\56\x67\157\x6f\144\163\x5f\x69\144\75{$goods_id}\x20\157\162\x64\145\162\x20\142\171\x20\157\56\x6f\162\144\145\162\x5f\x69\x64\x20\144\145\x73\143\40\x6c\151\155\x69\164\40{$limit}"; goto ia0EN; uMiR3: return $avatar_list; goto Wa8R9; ia0EN: $avatar_list = pdo_fetchall($sql); goto E6pwg; E6pwg: if (!(empty($avatar_list) || count($avatar_list) < $limit)) { goto iKSRx; } goto pyMFI; Hudms: Ljrum: goto TMWuk; pyMFI: $del = $limit - count($avatar_list); goto HjBMn; aH02_: foreach ($list as $val) { goto rhXt5; m6_xB: $tmp["\141\166\141\x74\x61\162"] = tomedia($val["\x61\x76\141\x74\141\162"]); goto tN3EP; rhXt5: $tmp = array(); goto m6_xB; tN3EP: $avatar_list[] = $tmp; goto L9Cpy; L9Cpy: QR08O: goto a5BMF; a5BMF: } goto Hudms; HjBMn: $list = pdo_fetchall("\x73\x65\x6c\x65\x63\164\x20\x2a\x20\x66\x72\157\155\40" . tablename("\154\x69\x6f\156\146\151\x73\150\x5f\143\x6f\155\x73\150\157\x70\x5f\x6a\151\x61\x75\x73\145\162") . "\x20\157\162\x64\x65\162\40\142\171\x20\x72\141\x6e\144\x28\x29\x20\154\151\x6d\151\x74\40{$del}"); goto aH02_; TMWuk: iKSRx: goto uMiR3; Wa8R9: } public function get_post_comment_info($post_id) { goto E7k2B; rZdx0: return $list; goto ZmHLB; E7k2B: $sql = "\163\145\x6c\145\x63\164\40\160\56\x2a\x2c\155\56\x75\163\145\162\156\x61\155\x65\x2c\155\x2e\x61\166\141\164\x61\x72\40\x66\162\157\x6d\40" . tablename("\x6c\x69\x6f\156\x66\151\163\x68\x5f\143\x6f\155\163\150\157\160\x5f\147\x72\157\165\160\137\154\172\x6c\137\162\145\x70\x6c\171") . "\x20\x70\54" . tablename("\x73\x6e\x61\151\x6c\146\151\x73\150\137\155\x65\155\142\145\162") . "\x20\141\x73\x20\155\40\x77\x68\x65\162\x65\40\160\56\155\145\155\x62\145\162\137\x69\x64\40\x3d\x20\x6d\56\x6d\x65\155\x62\x65\162\x5f\151\x64\x20\141\x6e\144\x20\x70\x2e\x70\157\x73\x74\x5f\x69\x64\40\75\40{$post_id}\x20\157\162\144\145\162\x20\x62\x79\40\x69\144\40\x61\163\x63\x20"; goto QCQJd; LIdgk: nf18U: goto rZdx0; QCQJd: $list = pdo_fetchall($sql); goto Ha1kc; Ha1kc: foreach ($list as $key => $val) { goto bbThR; TTieR: if (!($val["\164\x6f\x5f\155\145\155\x62\x65\162\x5f\x69\x64"] > 0)) { goto FRXtR; } goto fboY6; eUvn5: FRXtR: goto MYCpR; bbThR: $val["\143\157\x6e\x74\x65\x6e\x74"] = htmlspecialchars_decode($val["\143\157\156\164\x65\156\x74"]); goto TTieR; BYJ3A: $val["\164\x6f\137\x6d\x65\155\142\x65\162\x5f\x6e\141\155\145"] = $to_member_info["\x75\163\x65\x72\156\141\155\145"]; goto eUvn5; w0n8f: btmng: goto mhtYR; fboY6: $to_member_info = pdo_fetch("\x73\x65\154\x65\x63\164\x20\x75\x73\x65\x72\156\x61\155\x65\40\x66\162\x6f\155\x20" . tablename("\154\151\x6f\x6e\146\x69\163\150\x5f\x63\157\x6d\163\150\157\x70\x5f\x6d\x65\x6d\142\145\162") . "\x20\167\150\145\162\x65\40\155\145\155\x62\145\162\x5f\x69\x64\x3d{$val["\x74\157\x5f\155\145\x6d\142\145\x72\137\151\x64"]}"); goto BYJ3A; MYCpR: $list[$key] = $val; goto w0n8f; mhtYR: } goto LIdgk; ZmHLB: } public function get_post_fav_info($post_id) { goto Xhmg7; Xhmg7: $sql = "\x73\x65\154\145\x63\164\40\160\x2e\x2a\54\x6d\56\165\x73\x65\x72\156\141\155\x65\x2c\155\x2e\x61\x76\x61\x74\141\162\x20\x66\x72\x6f\x6d\40" . tablename("\x6c\151\157\156\x66\x69\163\x68\137\143\x6f\x6d\x73\x68\157\160\x5f\x67\162\157\x75\160\137\160\157\163\164\x5f\146\x61\166") . "\40\x70\x2c" . tablename("\163\156\141\x69\x6c\x66\x69\x73\150\x5f\x6d\x65\x6d\x62\x65\162") . "\40\141\163\40\x6d\x20\x77\x68\145\162\x65\40\x70\x2e\155\x65\x6d\x62\145\x72\137\x69\144\x20\x3d\x20\x6d\x2e\155\x65\155\142\x65\x72\137\151\x64\x20\141\x6e\x64\40\160\x2e\x70\x6f\x73\164\x5f\151\144\x20\75\x20{$post_id}"; goto Gioy2; Gioy2: $list = pdo_fetch($sql); goto CPGZ1; CPGZ1: return $list; goto u4H1R; u4H1R: } public function get_quan_info($where = array()) { $group_info = pdo_fetch("\163\x65\154\145\143\164\40\x2a\x20\x66\x72\157\x6d\x20" . tablename("\154\151\157\x6e\146\x69\163\x68\x5f\143\x6f\155\x73\150\157\x70\137\147\x72\x6f\165\160") . $where); return $group_info; } public function get_goods_info($where = array()) { goto QeDAv; hish9: $goods_info["\144\x61\156\160\162\x69\x63\145"] = $price_arr["\144\x61\x6e\x70\x72\151\x63\145"]; goto Ss0ut; QeDAv: $goods_info = pdo_fetch("\x73\x65\154\145\143\x74\x20\151\x64\54\163\x65\x6c\154\145\162\x5f\x63\x6f\x75\156\x74\54\163\141\154\145\x73\x2c\147\157\157\x64\163\x6e\x61\x6d\x65\40\x66\x72\x6f\x6d\40" . tablename("\154\151\x6f\x6e\146\x69\x73\150\x5f\x63\x6f\155\x73\150\157\160\x5f\147\157\x6f\144\x73") . $where); goto xz216; FGWv3: return $goods_info; goto fxlAP; KRrEd: $goods_info["\156\x61\x6d\145"] = $goods_info["\x67\157\157\x64\x73\x6e\x61\x6d\x65"]; goto FGWv3; CNdq_: $goods_info["\163\145\x6c\x6c\x65\x72\137\x63\x6f\165\x6e\164"] += $goods_info["\163\x61\154\x65\163"]; goto KRrEd; Ss0ut: $goods_info["\x70\x72\151\143\x65"] = $price_arr["\160\x72\151\143\x65"]; goto CNdq_; j32qC: if (empty($good_image)) { goto NrhKS; } goto DdjPq; YSKAZ: NrhKS: goto q7jO5; DdjPq: $goods_info["\151\155\141\x67\145"] = tomedia($good_image["\x69\x6d\x61\147\145"]); goto YSKAZ; xz216: $good_image = load_model_class("\160\x69\x6e\147\157\x6f\x64\x73")->get_goods_images($goods_info["\151\x64"]); goto j32qC; q7jO5: $price_arr = load_model_class("\x70\x69\x6e\147\157\x6f\144\x73")->get_goods_price($goods_info["\x69\x64"]); goto hish9; fxlAP: } public function load_dynamic_post($goods_id, $post_id, $up_down, $limit = 10, $is_goods_info = 1) { goto QHBJa; i3x54: FIudm: goto ebiRK; R0dOc: XpgX7: goto AJZnI; AJZnI: foreach ($list as $key => $val) { goto XZDL_; QV3Fh: if (!($is_goods_info == 1)) { goto QYR79; } goto mc_2t; uN53R: $val["\164\x69\164\154\x65"] = htmlspecialchars_decode($val["\x74\151\164\x6c\145"]); goto Rc3v6; mc_2t: $val["\143\157\155\x6d\145\156\x74\137\154\x69\163\164"] = $this->get_post_comment_info($val["\151\x64"]); goto o59A9; KG0HS: $list[$key] = $val; goto C4p10; LYcea: $val["\x67\157\x6f\x64\x73\x5f\151\x6e\x66\157"] = $this->get_goods_info($where); goto Srn6K; Rc3v6: $val["\146\141\166\x5f\x6c\x69\163\x74"] = $this->get_post_fav_info($val["\151\x64"]); goto QV3Fh; C4p10: ecnrK: goto Clpj6; o59A9: if (!($val["\x67\157\x6f\144\163\x5f\x69\144"] != 0)) { goto QNsKL; } goto FaLiB; Srn6K: QNsKL: goto kr1h8; FaLiB: $where = "\167\150\x65\162\x65\40\151\144\x3d" . $val["\x67\157\157\144\163\137\x69\x64"]; goto LYcea; XZDL_: $has_fav = pdo_fetch("\x73\x65\x6c\145\x63\164\40\52\x20\x66\162\157\x6d\40" . tablename("\154\x69\157\x6e\x66\x69\x73\150\x5f\x63\157\155\x73\x68\x6f\x70\x5f\x67\x72\157\165\x70\137\160\x6f\163\x74\137\x66\x61\x76") . "\167\x68\145\x72\145\x20\x70\x6f\x73\164\137\x69\x64\x3d{$val["\151\144"]}\40\x61\156\x64\40\155\145\x6d\142\145\162\x5f\151\144\x3d{$member_id}"); goto aND3g; aND3g: $val["\x68\x61\163\x5f\x66\x61\166"] = empty($has_fav) ? false : true; goto uN53R; kr1h8: QYR79: goto KG0HS; Clpj6: } goto i3x54; eeCXH: $order_by = "\40\160\56\151\x64\40\144\145\x73\x63\x20"; goto oR673; oR673: goto Rf4xe; goto EJOD5; jfZpA: $order_by = "\x20\160\56\151\144\40\144\145\x73\x63\x20"; goto pzKck; Fse6q: $sql = "\163\x65\x6c\145\143\164\40\x70\x2e\52\54\155\x2e\x75\x73\145\x72\156\x61\155\145\54\x6d\x2e\x61\x76\141\164\x61\x72\x20\146\x72\157\155\x20" . tablename("\154\151\157\x6e\x66\151\x73\x68\x5f\x63\157\155\163\150\x6f\160\x5f\147\162\x6f\165\160\137\160\x6f\163\x74") . "\40\x70\x2c" . tablename("\163\156\141\x69\x6c\146\151\x73\x68\137\155\145\x6d\142\x65\x72") . "\40\141\x73\x20\x6d\x20\167\150\145\x72\x65\40\160\56\155\x65\155\x62\145\162\137\151\x64\40\75\x20\x6d\x2e\x6d\x65\x6d\x62\x65\162\x5f\x69\x64\40{$where}\x20\157\162\144\x65\162\40\142\x79\x20{$order_by}\40\x6c\x69\155\x69\164\x20{$limit}\40"; goto pHhFx; hlNOK: HXD0o: goto jfZpA; I_tbb: die; goto R0dOc; pzKck: Rf4xe: goto Fse6q; YBXgo: $where .= "\40\141\156\x64\40\160\x2e\151\x64\x20\76{$post_id}\x20"; goto eeCXH; EJOD5: UWXth: goto WG82J; rpJLD: $order_by = ''; goto azuvN; dGoPr: if ($member_id) { goto XpgX7; } goto uBphl; WG82J: if (!($post_id > 0)) { goto HXD0o; } goto IqbMa; azuvN: if ($up_down == 1) { goto UWXth; } goto YBXgo; ebiRK: return $list; goto OgmAI; QHBJa: $where = "\40\141\156\144\x20\160\56\147\157\x6f\x64\163\x5f\x69\x64\40\x3d\x20{$goods_id}\40"; goto rpJLD; uBphl: echo json_encode(array("\x63\157\144\x65" => 0, "\x6d\163\147" => "\346\202\250\xe6\234\252\347\x99\273\xe5\275\225")); goto I_tbb; ZHIce: $member_id = $this->get_memberid(); goto dGoPr; IqbMa: $where .= "\40\x61\156\x64\40\160\56\151\144\40\x3c{$post_id}\40"; goto hlNOK; pHhFx: $list = pdo_fetchall($sql); goto ZHIce; OgmAI: } public function load_dynamic_view($id, $post_id) { goto e23KZ; e23KZ: $where = "\x20\x61\156\144\x20\160\56\151\x64\x20\x3d\40{$id}\40"; goto UzRRA; aW4A6: echo json_encode(array("\143\157\x64\145" => 0, "\155\163\x67" => "\346\202\250\xe6\234\252\xe7\231\273\345\275\x95")); goto rfYym; Jjojw: $member_id = $this->get_memberid(); goto hdHZm; RKps5: foreach ($list as $key => $val) { goto h9ZeF; cRPq4: BUgi9: goto oZsHj; jY1M_: $val["\164\x69\164\154\145"] = htmlspecialchars_decode($val["\164\151\164\154\x65"]); goto Rk3B3; Isr9K: $val["\x68\141\x73\x5f\146\141\x76"] = empty($has_fav) ? false : true; goto jY1M_; mNwNj: w7o2D: goto raJeH; FOrfQ: $where = "\167\150\x65\x72\145\x20\x69\x64\x3d" . $val["\x67\x6f\x6f\x64\x73\137\151\144"]; goto lzMDJ; lzMDJ: $val["\147\157\x6f\144\x73\137\151\x6e\146\x6f"] = $this->get_goods_info($where); goto cRPq4; oZsHj: $list[$key] = $val; goto mNwNj; Rk3B3: $val["\x66\141\x76\x5f\x6c\x69\x73\164"] = $this->get_post_fav_info($val["\151\x64"]); goto xX5Xk; xX5Xk: $val["\x63\x6f\155\155\145\x6e\164\x5f\154\x69\x73\164"] = $this->get_post_comment_info($val["\x69\x64"]); goto lYZ9x; h9ZeF: $has_fav = pdo_fetch("\163\145\154\x65\143\x74\x20\x2a\x20\146\x72\x6f\155\x20" . tablename("\154\x69\157\x6e\146\x69\163\150\x5f\x63\x6f\x6d\x73\150\x6f\x70\137\147\162\x6f\x75\x70\137\160\x6f\x73\x74\137\146\x61\166") . "\x77\150\145\162\x65\40\x70\x6f\x73\164\137\151\x64\x3d{$val["\151\144"]}\40\x61\x6e\x64\x20\155\145\155\x62\145\162\137\x69\144\75{$member_id}"); goto Isr9K; lYZ9x: if (!($val["\x67\x6f\157\144\163\137\x69\x64"] != 0)) { goto BUgi9; } goto FOrfQ; raJeH: } goto Bmh16; hdHZm: if ($member_id) { goto HimKA; } goto aW4A6; wmjZ3: return $list; goto k1GCA; jnr7t: $sql = "\163\145\x6c\145\x63\x74\40\x70\x2e\52\54\x6d\x2e\x75\163\x65\x72\156\x61\155\145\54\155\x2e\141\166\x61\164\141\x72\x20\146\162\157\x6d\x20" . tablename("\x6c\151\157\x6e\x66\151\x73\150\x5f\x63\157\155\x73\150\157\x70\x5f\x67\x72\x6f\165\160\x5f\x70\157\163\164") . "\40\160\x2c" . tablename("\x73\x6e\141\151\x6c\146\x69\x73\150\137\x6d\145\x6d\x62\145\162") . "\x20\141\x73\x20\155\x20\167\150\145\x72\145\x20\160\56\155\145\x6d\x62\145\162\x5f\151\144\x20\x3d\x20\155\56\155\145\x6d\142\x65\162\137\151\x64\40{$where}\40\154\x69\155\x69\x74\40\x31"; goto UVcPj; ZUpVp: HimKA: goto RKps5; UVcPj: $list = pdo_fetchall($sql); goto Jjojw; rfYym: die; goto ZUpVp; Bmh16: BRHfu: goto wmjZ3; UzRRA: $order_by = ''; goto jnr7t; k1GCA: } }