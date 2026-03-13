<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
 include_once __DIR__ . '/compents/header.php'; 
 require_once __DIR__ . '/config/connection.php';
 require_once __DIR__ . '/controllers/UserController.php';
 require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/models/TicketModel.php';

// Ticket reporting & analytics
$ticketModel = new TicketModel($pdo);

// Total tickets
$totalTickets = (int) $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();

// Tickets by status
$statusStats = $pdo->query("
    SELECT status, COUNT(*) AS total 
    FROM tickets 
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Tickets by priority
$priorityStats = $pdo->query("
    SELECT priority, COUNT(*) AS total 
    FROM tickets 
    GROUP BY priority
")->fetchAll(PDO::FETCH_ASSOC);

// Total messages (all comments on tickets)
$totalMessages = (int) $pdo->query("
    SELECT COUNT(*) 
    FROM ticket_comments
")->fetchColumn();

$avgFirstResponseMinutes = $pdo->query("
    SELECT AVG(first_response_minutes) 
    FROM (
        SELECT 
            tickets.ticket_id,
            TIMESTAMPDIFF(
                MINUTE,
                tickets.created_at,
                MIN(ticket_comments.created_at)
            ) AS first_response_minutes
        FROM tickets 
        JOIN ticket_comments  
            ON ticket_comments.ticket_id = tickets.ticket_id
           AND ticket_comments.agent_id IS NOT NULL
        GROUP BY tickets.ticket_id
    ) AS responses
")->fetchColumn();

$avgFirstResponseMinutes = $avgFirstResponseMinutes !== null ? (float) $avgFirstResponseMinutes : 0.;


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
	<meta name="author" content="AdminKit">
	<meta name="keywords"
		content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />

	<link rel="canonical" href="https://demo-basic.adminkit.io/" />

	<title>Ticketing System</title>

	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/custom.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
	

			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3"><strong>Ticket</strong> Analytics</h1>

					<div class="row">
						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card ticket-stat-card stat-open">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">
															<i class="align-middle me-2" data-feather="ticket" style="width: 18px; height: 18px;"></i>
															Total Tickets
														</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="ticket"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3 ticket-stat-number">
													<?= (int) $totalTickets ?>
												</h1>
												<div class="ticket-stat-label">All Tickets</div>
											</div>
										</div>
										<div class="card ticket-stat-card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">
															<i class="align-middle me-2" data-feather="message-circle" style="width: 18px; height: 18px;"></i>
															Total Messages
														</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="message-circle"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3 ticket-stat-number">
													<?= (int) $totalMessages ?>
												</h1>
												<div class="ticket-stat-label">All Comments</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card ticket-stat-card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">
															<i class="align-middle me-2" data-feather="clock" style="width: 18px; height: 18px;"></i>
															Avg. First Response
														</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="clock"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3 ticket-stat-number">
													<?= number_format($avgFirstResponseMinutes, 1) ?> <small style="font-size: 1.5rem;">min</small>
												</h1>
												<div class="ticket-stat-label">Response Time</div>
											</div>
										</div>
										<div class="card ticket-stat-card stat-in-progress">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">
															<i class="align-middle me-2" data-feather="alert-triangle" style="width: 18px; height: 18px;"></i>
															Critical Tickets
														</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="alert-triangle"></i>
														</div>
													</div>
												</div>
												<?php
												$criticalTickets = 0;
												foreach ($priorityStats as $row) {
													if (in_array(strtolower($row['priority']), ['high', 'urgent'], true)) {
														$criticalTickets += (int) $row['total'];
													}
												}
												?>
												<h1 class="mt-1 mb-3 ticket-stat-number">
													<?= (int) $criticalTickets ?>
												</h1>
												<div class="ticket-stat-label">High & Urgent</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-xxl-7">
							<div class="card flex-fill w-100">
								<div class="card-header">
									<h5 class="card-title mb-0">
										<i class="align-middle me-2" data-feather="pie-chart"></i>
										Tickets by Status
									</h5>
								</div>
								<div class="card-body py-3">
									<?php if (!empty($statusStats)): ?>
										<div class="chart chart-sm">
											<canvas id="ticketStatusPieChart"></canvas>
										</div>
									<?php else: ?>
										<p class="text-center text-muted mb-0">No tickets yet.</p>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="row">




			</main>

			<footer class="footer">
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-start">
							<p class="mb-0">
								<a class="text-muted" href=""
									target="_blank"><strong></strong></a> <a class="text-muted"
									href="" target="_blank"><strong>Morgan's Ticketing System</strong></a> &copy;
							</p>
						</div>
						<div class="col-6 text-end">
							<ul class="list-inline">
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Support</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Help Center</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Privacy</a>
								</li>
								<li class="list-inline-item">
									<a class="text-muted" href="https://adminkit.io/" target="_blank">Terms</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	<!-- Chart.js CDN (fallback if not in app.js) -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
	<script src="js/app.js"></script>

	<script>
		// Wait for Chart.js to be available
		document.addEventListener('DOMContentLoaded', function() {
			<?php if (!empty($statusStats)): ?>
			// Check if Chart is available
			if (typeof Chart === 'undefined') {
				console.error('Chart.js is not loaded');
				return;
			}
			
			const statusData = <?= json_encode($statusStats) ?>;
			
			// Extract labels and data
			const statusLabels = statusData.map(item => item.status);
			const statusCounts = statusData.map(item => parseInt(item.total));
			
			// Color palette for different statuses
			const statusColors = {
				'open': '#3b82f6',      // Blue
				'in_progress': '#f59e0b', // Amber
				'resolved': '#10b981',    // Green
				'closed': '#6b7280',      // Gray
				'pending': '#ef4444'       // Red
			};
			
			// Generate colors array based on status
			const backgroundColors = statusLabels.map(status => {
				const normalizedStatus = status.toLowerCase().replace(/\s+/g, '_');
				return statusColors[normalizedStatus] || '#8b5cf6'; // Default purple
			});
			
			// Create pie chart
			const ctx = document.getElementById('ticketStatusPieChart');
			if (ctx) {
				new Chart(ctx, {
					type: 'pie',
					data: {
						labels: statusLabels,
						datasets: [{
							data: statusCounts,
							backgroundColor: backgroundColors,
							borderColor: '#ffffff',
							borderWidth: 2
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: true,
						legend: {
							position: 'bottom',
							labels: {
								padding: 15,
								usePointStyle: true
							}
						},
						tooltips: {
							callbacks: {
								label: function(tooltipItem, data) {
									const label = data.labels[tooltipItem.index] || '';
									const value = data.datasets[0].data[tooltipItem.index];
									const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
									const percentage = ((value / total) * 100).toFixed(1);
									return label + ': ' + value + ' (' + percentage + '%)';
								}
							}
						}
					}
				});
			}
			<?php endif; ?>
		});
	</script>

</body>
</html>