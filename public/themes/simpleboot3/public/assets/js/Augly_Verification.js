<<<<<<< HEAD
//正整数
var AuglyTest_int=/^[0-9]*[1-9][0-9]*$/;
//负整数
var AuglyTest_revint=/^[0-9]*[1-9][0-9]*$/;
//正浮点数
var AuglyTest_float=/^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/;   
//负浮点数
var AuglyTest_revfloat=/^(-(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*)))$/;  
//浮点数
var AuglyTest_allfloat=/^(-?\d+)(\.\d+)?$/;
//email地址
var AuglyTest_email=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
//年/月/日（年-月-日、年.月.日）
var AuglyTest_Data=/^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$/;
//匹配中文字符
var AuglyTest_char=/[\u4e00-\u9fa5]/;
//匹配帐号是否合法(字母开头，允许5-10字节，允许字母数字下划线)
var AuglyTest_allow=/^[a-zA-Z][a-zA-Z0-9_]{4,9}$/;
//匹配空白行的正则表达式
var AuglyTest_blank= /\n\s*\r/;
//匹配中国邮政编码
var AuglyTest_PostalCode=/[1-9]\d{5}(?!\d)/;
//匹配身份证
var AuglyTest_ID=/\d{15}|\d{18}/;
//匹配国内电话号码
var AuglyTest_tel=/(\d{3}-|\d{4}-)?(\d{8}|\d{7})?/;
//匹配IP地址
var AuglyTest_IP=/((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/;
//匹配首尾空白字符的正则表达式
var AuglyTest_trim= /^\s*|\s*$/;
//验证手机号
var AuglyTest_phone=/^1(3|4|5|7|8)\d{9}$/;
//验证腾讯QQ号
var AuglyTest_QQ= /^[1-9]*[1-9][0-9]*$/;
//验证中文，英文，数字，下划线
var AuglyTest_all=/^[\u4e00-\u9fa5_a-zA-Z0-9]+$/;
=======
//正整数
var AuglyTest_int=/^[0-9]*[1-9][0-9]*$/;
//负整数
var AuglyTest_revint=/^[0-9]*[1-9][0-9]*$/;
//正浮点数
var AuglyTest_float=/^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/;   
//负浮点数
var AuglyTest_revfloat=/^(-(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*)))$/;  
//浮点数
var AuglyTest_allfloat=/^(-?\d+)(\.\d+)?$/;
//email地址
var AuglyTest_email=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
//年/月/日（年-月-日、年.月.日）
var AuglyTest_Data=/^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$/;
//匹配中文字符
var AuglyTest_char=/[\u4e00-\u9fa5]/;
//匹配帐号是否合法(字母开头，允许5-10字节，允许字母数字下划线)
var AuglyTest_allow=/^[a-zA-Z][a-zA-Z0-9_]{4,9}$/;
//匹配空白行的正则表达式
var AuglyTest_blank= /\n\s*\r/;
//匹配中国邮政编码
var AuglyTest_PostalCode=/[1-9]\d{5}(?!\d)/;
//匹配身份证
var AuglyTest_ID=/\d{15}|\d{18}/;
//匹配国内电话号码
var AuglyTest_tel=/(\d{3}-|\d{4}-)?(\d{8}|\d{7})?/;
//匹配IP地址
var AuglyTest_IP=/((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/;
//匹配首尾空白字符的正则表达式
var AuglyTest_trim= /^\s*|\s*$/;
//验证手机号
var AuglyTest_phone=/^1(3|4|5|7|8)\d{9}$/;
//验证腾讯QQ号
var AuglyTest_QQ= /^[1-9]*[1-9][0-9]*$/;
//验证中文，英文，数字，下划线
var AuglyTest_all=/^[\u4e00-\u9fa5_a-zA-Z0-9]+$/;
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
