<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($autorefresh): ?>
        <meta http-equiv="refresh" content="30">
    <?php endif; ?>

    <title>Pulls Requests</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        body {
            padding: 10px;
            padding-top: 70px;
            font-size: 12px;
        }

        h4 {
            font-size: 1.4rem;
        }

        .panel > .list-group .list-group-item.updated {
            border-left: 3px solid #0366d6;
        }

        .col-md-3 {
            padding-right: 5px;
            padding-left: 5px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#pr-filters" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="pr-filters">
            <form class="navbar-form navbar-left" role="filter">
                <select multiple class="form-control select2" name="label[]" placeholder="Excluded labels">
                    <?php foreach ($labels as $label): ?>
                    <option value="<?php echo $label ?>" <?php if (in_array($label, $selectedLabels)): ?>selected<?php endif; ?>><?php echo $label ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-control" name="status" placeholder="Required status">
                    <option value="" <?php if ($selectedStatus == ''): ?>selected<?php endif; ?>>All</option>
                    <option value="success" <?php if ($selectedStatus == 'success'): ?>selected<?php endif; ?>>Success</option>
                    <option value="failure" <?php if ($selectedStatus == 'failure'): ?>selected<?php endif; ?>>Failure</option>
                    <option value="pending" <?php if ($selectedStatus == 'pending'): ?>selected<?php endif; ?>>Pending</option>
                </select>
                <input type="text" class="form-control" name="notApprovedBy" placeholder="Excluded approver" value="<?php echo $notApprovedBy; ?>"/>
                <input type="text" class="form-control" name="notAuthoredBy" placeholder="Excluded author" value="<?php echo $notAuthoredBy; ?>"/>
                <label>
                    <input type="checkbox" name="hideNonUpdated" <?php if ($hideNonUpdated): ?>checked<?php endif; ?>> Hide non updated
                </label>
                <label>
                    <input type="checkbox" name="autorefresh" <?php if ($autorefresh): ?>checked<?php endif; ?>> Autorefresh
                </label>
                <button type="submit" class="btn btn-default">Filter</button>
            </form>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <?php foreach ($repos as $repository => $pullRequests): ?>
            <?php if (count($pullRequests)): ?>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo $repository; ?>
                        <span class="badge pull-right"><?php echo (string) count($pullRequests); ?></span>
                    </div>
                    <div class="list-group">
                        <?php foreach ($pullRequests as $pr):
                            if (array_intersect($selectedLabels, array_column($pr['issue']['labels'], 'name'))) {
                                continue;
                            }
                            if (!empty($selectedStatus) && $selectedStatus != $pr['status']['state']) {
                                continue;
                            }
                            $updated = !(!empty($_COOKIE['lastClick'][$pr['id']]) && $_COOKIE['lastClick'][$pr['id']] > strtotime($pr['updated_at']));

                            if (!empty($notApprovedBy) && isset($pr['reviews']['approvers'][$notApprovedBy])) {
                                continue;
                            }

                            if (!$updated && $hideNonUpdated) {
                                continue;
                            }

                            if (!empty($notAuthoredBy) && $pr['user']['login'] === $notAuthoredBy) {
                                continue;
                            }
                            ?>
                            <a href="/<?php echo $pr['id']; ?>/<?php echo base64_encode($pr['html_url']); ?>" class="list-group-item <?php if ($updated): ?>updated<?php endif; ?> <?php if ($pr['reviews']['state'] == 'APPROVED'): ?>list-group-item-success<?php endif; ?>" target="_blank">
                                <div class="media">
                                    <div class="media-left">
                                        <img class="media-object img-rounded" src="<?php echo $pr['user']['avatar_url']; ?>" alt="<?php echo $pr['user']['login']; ?>" width="40">
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            <?php echo $pr['title'] ?> <i class="fa <?php echo status_icon($pr['status']['state']); ?>" aria-hidden="true"></i>
                                            <small>
                                                <?php foreach ($pr['issue']['labels'] as $label): ?>
                                                    <span class="label" style="background-color:#<?php echo $label['color'] ?>; color: <?php echo font_color($label['color']) ?>"><?php echo $label['name'] ?></span>
                                                <?php endforeach; ?>
                                            </small>
                                        </h4>
                                        <span class="text-muted">
                                            #<?php echo $pr['number'] ?> <i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date('d/m/Y H:i', strtotime($pr['created_at'])); ?> <i class="fa fa-comment-o" aria-hidden="true"></i> <?php echo isset($pr['comments']) ? count($pr['comments']) : '0' ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();
</script>
</body>
</html>
