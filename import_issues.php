<?php
require('config.php');
require('markdownify/markdownify_extra.php');
require('curl_github.php');
$md = new Markdownify(FALSE,FALSE,FALSE);
$doc = new DOMDocument();
$doc->load($issuesXML);
$issues = $doc->getElementsByTagName('item');
$issueCount = $issues->length;
for ($i=$issues->length-1; $i>=0; $i--) {
  $issue = array('labels'=>array());
  foreach ($issues->item($i)->childNodes as $node) {
    if ($node->nodeName == 'comments') {
      if (trim($node->textContent)) $issue[$node->nodeName] = $node->childNodes;
    } elseif ($node->nodeName =='attachments') {
      if ($node->childNodes->length > 1) $issue['attachments'] = $node->childNodes;
    } elseif ($node->nodeName == 'component') {
      $issue['labels'][] = sanitizeLabel($node->nodeValue);
    } else {
      $issue[$node->nodeName] = trim($node->nodeValue);
    }
    if ($node->nodeName == 'status') {
      $issue['statusid'] = $node->attributes->getNamedItem('id')->value;
    }
  }
  $resolved = ($issue['statusid'] != 1 && $issue['statusid'] != 3);
  
  $json = array();
  $json['title'] = sprintf('[%s] %s', $issue['key'], $issue['summary']);
  
  $json['labels'] = $issue['labels'];
  $json['labels'][] = sanitizeLabel($issue['type']);
  
  $description = $md->parseString($issue['description']);
  if (isset($issue['attachments'])) {
    $attachments = array();
    foreach ($issue['attachments'] as $node) {
      if ($node->nodeName == 'attachment') {
        $attachments[] = $node->attributes->getNamedItem('name')->value;
      }
    }
    $attachments = implode(', ', $attachments);
  } else {
    unset($attachments);
  }
  
  $body = "> **Priority**: ${issue['priority']}\n";
  if ($resolved)
    $body .= "> **Resolution**: ${issue['resolution']} (on ${issue['resolved']})\n";
  $body .= "> **Original Assignee**: ${issue['assignee']}\n";
  $body .= "> **Reporter**: ${issue['reporter']}\n";
  $body .= "> **Created At**: ${issue['created']}\n";
  $body .= "> **Last Updated on Jira**: ${issue['updated']}\n";
  if (isset($attachments)) {
    $body .= "> **Attachments (unavailable)**: $attachments\n";
  }
  
  $body .= "\n" . $description;
  
  if (isset($issue['comments'])) {
    $body .= "\n\nComments:\n";
    foreach ($issue['comments'] as $node) {
      if ($node->nodeName == 'comment') {
        $author = $node->attributes->getNamedItem('author')->value;
        $created = $node->attributes->getNamedItem('created')->value;
        $body .= "\n**$author** on $created\n\n" . $md->parseString('<blockquote>'.$node->textContent.'</blockquote>') . "\n";
      }
    }
  }
  
  $json['body'] = $body;
  
  // post to github
  $githubIssue = createIssue($json);
  if ($resolved && isset($githubIssue->number)) closeIssue($githubIssue->number);
  echo 'Imported [' . $issue['key'] . "]\n";
}
