[93m[0m[93m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[0m
[93mmodified: tests/Unit/ReleaseInsights/BugzillaTest.php
[93m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[0m
[1;35m[1;35m@ tests/Unit/ReleaseInsights/BugzillaTest.php:6 @[1m[1m[38;5;146m declare(strict_types=1);[0m
[m
use ReleaseInsights\Bugzilla as bz;[m
[m
[1;31m[1;31mtest('[m[1;31;48;5;52m[m[1;31mgetBugListLink' , function () {[m
[0m[1;32m[1;32mtest('[m[1;32;48;5;22mBugzilla::[m[1;32mgetBugListLink' , function () {[m
[0m    $this->assertEquals([m
        'https://bugzilla.mozilla.org/buglist.cgi?bug_id=101%2C102%2C103',[m
        bz::getBugListLink([101, 102, 103])[m
[93m[0m[93m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[0m
[93mmodified: tests/Unit/ReleaseInsights/UtilsTest.php
[93m────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────[0m
[1;35m[1;35m@ tests/Unit/ReleaseInsights/UtilsTest.php:10 @[1m[1m[38;5;146m CONST FIREFOX_RELEASE = '93';[0m
CONST FIREFOX_BETA = '94';[m
CONST FIREFOX_NIGHTLY = '95';[m
[m
[1;31m[1;31mtest('[m[1;31;48;5;52m[m[1;31mrequestedVersion' , function () {[m
[0m[1;32m[1;32mtest('[m[1;32;48;5;22mUtils::[m[1;32mrequestedVersion' , function () {[m
[0m    $this->assertEquals('94.0', U::requestedVersion());[m
    $this->assertEquals('95.0', U::requestedVersion(FIREFOX_NIGHTLY));[m
    $this->assertEquals('94.0', U::requestedVersion(FIREFOX_BETA));[m
[1;35m[1;35m@ tests/Unit/ReleaseInsights/UtilsTest.php:18 @[1m[1m[38;5;146m test('requestedVersion' , function () {[0m
    $this->assertEquals('100.0', U::requestedVersion('100'));[m
});[m
[m
[1;31m[1;31mtest('[m[1;31;48;5;52m[m[1;31misBuildID' , function () {[m
[0m[1;32m[1;32mtest('[m[1;32;48;5;22mUtils::[m[1;32misBuildID' , function () {[m
[0m    $this->assertFalse(U::isBuildID('01234587392871'));[m
    $this->assertFalse(U::isBuildID('oajoaoojoaooao'));[m
    $this->assertFalse(U::isBuildID('0123458739287122'));[m
[1;35m[1;35m@ tests/Unit/ReleaseInsights/UtilsTest.php:27 @[1m[1m[38;5;146m test('isBuildID' , function () {[0m
    $this->assertTrue(U::isBuildID('20201229120000'));[m
});[m
[m
[1;31m[1;31mtest('getDate' , function () {[m
[0m[1;32m[1;32m[m[1;32mtest('Utils::getBuildID' , function () {[m
[0m[1;32m[1;32m[m[1;32m    // Test fallback value[m
[0m[1;32m[1;32m[m[1;32m    $this->assertEquals('20191014213051', U::getBuildID('20501229120000'));[m
[0m[7m[1;32m [m
[1;32m[1;32m[m[1;32m    // Test good value[m
[0m[1;32m[1;32m[m[1;32m    $this->assertEquals('20201229120000', U::getBuildID('20201229120000'));[m
[0m[1;32m[1;32m[m[1;32m});[m
[0m[7m[1;32m [m
[1;32m[1;32m[m[1;32mtest('Utils::secureText', function ($input, $output) {[m
[0m[1;32m[1;32m[m[1;32m    expect($output)->toEqual(U::secureText($input));[m
[0m[1;32m[1;32m[m[1;32m})->with([[m
[0m[1;32m[1;32m[m[1;32m    ["achat des couteaux\nsuisses", 'achat des couteaux suisses'],[m
[0m[1;32m[1;32m[m[1;32m    ['<b>foo</b>', '&#60;b&#62;foo&#60;/b&#62;'],[m
[0m[1;32m[1;32m[m[1;32m]);[m
[0m[7m[1;32m [m
[1;32m[1;32m[m[1;32mtest('Utils::getDate' , function () {[m
[0m[m
    // No get parameter, Today[m
    $this->assertEquals(date('Ymd'), U::getDate());[m
