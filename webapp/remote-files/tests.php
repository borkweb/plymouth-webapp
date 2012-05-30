<?php

define('EXCEPTION', null);

$test_paths = array(
    '/home/psutest2/banjobs/backup/testthing/stuff' => '/home/psutest2/banjobs/backup/',
    '/home/psutest2/banjobs/backup/file..mp3' => '/home/psutest2/banjobs/backup/',
    '/home/psutest2/banjobs/backup/..mp3' => '/home/psutest2/banjobs/backup/',
    '/home/psutest2/banjobs/backup/file..' => '/home/psutest2/banjobs/backup/',
    '/home/psutest2/banjobs/backup../file' => '/home/psutest2/banjobs/',
    '/home/psutest2/banjobs/' => '/home/psutest2/banjobs/',
    '/home/psutest2/' => false,
    'test/dir/' => EXCEPTION,
    '../' => EXCEPTION,
    '/home/psutest2/banjobs/../' => EXCEPTION,
    '/home/psutest2/banjobs/backup/\\.\\./' => EXCEPTION
);

echo '<table class="tests"><thead><tr><th>Argument</th><th>Expected</th><th>Result</th>';
foreach($test_paths as $path => $expected)
{
    echo "\n<tr>\n<td>$path</td>";
    try
    {
        $result = $GLOBALS['RFP']->directoryForPath($path);
        echo '<td>';
        echo $result == null ? '<b>no match</b>' : $result['path'];
        echo '</td>';

        if($result['path'] == $expected)
        {
            echo '<td>pass</td>';
        }
        else
        {
            echo '<td>fail</td>';
        }
    }
    catch(RFException $e)
    {
        echo '<td><b>Exception:</b> ' . $e->getMessage() . '</td>';

        if($expected == EXCEPTION)
        {
            echo '<td>pass</td>';
        }
        else
        {
            echo '<td>fail</td>';
        }
    }
    echo "</tr>";
}
echo '</table>';
