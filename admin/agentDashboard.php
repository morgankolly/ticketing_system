<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

 include_once __DIR__ . '/compents/agentHeader.php'; 
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/models/TicketModel.php';
require_once __DIR__ . '/models/UserModel.php';


$ticketModel = new TicketModel($pdo);
$userModel = new UserModel($pdo);
$agentId = $_SESSION['user_id'] ?? null;

// Get agent's assigned tickets
$assignedTickets = $ticketModel->getAssignedTickets($agentId);

// Calculate stats
$totalAssigned = count($assignedTickets);
$openTickets = count(array_filter($assignedTickets, fn($t) => strtolower($t['status']) === 'open'));
$inProgressTickets = count(array_filter($assignedTickets, fn($t) => strtolower($t['status']) === 'in_progress' || strtolower($t['status']) === 'in progress'));
$resolvedTickets = count(array_filter($assignedTickets, fn($t) => strtolower($t['status']) === 'resolved'));
$closedTickets = count(array_filter($assignedTickets, fn($t) => strtolower($t['status']) === 'closed'));

// Get agent info
$agent = $userModel->getUserById($agentId);
?>

			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3" style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
						<strong>Agent</strong> Dashboard
						<?php if ($agent): ?>
							<small class="text-white-50">- Welcome, <?= htmlspecialchars($agent['user_name']) ?>!</small>
						<?php endif; ?>
					</h1>

					<div class="row">
						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Total Assigned</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="inbox"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3"><?= $totalAssigned ?></h1>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Open Tickets</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="alert-circle"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3"><?= $openTickets ?></h1>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">In Progress</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="clock"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3"><?= $inProgressTickets ?></h1>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Resolved</h5>
													</div>
													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="check-circle"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3"><?= $resolvedTickets ?></h1>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-xxl-7">
							<div class="card flex-fill w-100">
								<div class="card-header">
									<h5 class="card-title mb-0">My Tickets Status</h5>
								</div>
								<div class="card-body py-3">
									<?php 
									$statusData = [
										['status' => 'Open', 'total' => $openTickets],
										['status' => 'In Progress', 'total' => $inProgressTickets],
										['status' => 'Resolved', 'total' => $resolvedTickets],
										['status' => 'Closed', 'total' => $closedTickets]
									];
									$statusData = array_filter($statusData, fn($s) => $s['total'] > 0);
									?>
									<?php if (!empty($statusData)): ?>
										<div class="chart chart-sm">
											<canvas id="agentStatusPieChart"></canvas>
										</div>
									<?php else: ?>
										<p class="text-center text-muted mb-0">No tickets assigned yet.</p>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="row">




						<div class="row">
							<div class="col-12 col-lg-8 col-xxl-9 d-flex">
								<div class="card flex-fill">
									<div class="card-header">

										<h5 class="card-title mb-0">Latest Projects</h5>
									</div>
									<table class="table table-hover my-0">
										<thead>
											<tr>
												<th>Name</th>
												<th class="d-none d-xl-table-cell">Start Date</th>
												<th class="d-none d-xl-table-cell">End Date</th>
												<th>Status</th>
												<th class="d-none d-md-table-cell">Assignee</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Project Apollo</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-success">Done</span></td>
												<td class="d-none d-md-table-cell">Vanessa Tucker</td>
											</tr>
											<tr>
												<td>Project Fireball</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-danger">Cancelled</span></td>
												<td class="d-none d-md-table-cell">William Harris</td>
											</tr>
											<tr>
												<td>Project Hades</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-success">Done</span></td>
												<td class="d-none d-md-table-cell">Sharon Lessman</td>
											</tr>
											<tr>
												<td>Project Nitro</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-warning">In progress</span></td>
												<td class="d-none d-md-table-cell">Vanessa Tucker</td>
											</tr>
											<tr>
												<td>Project Phoenix</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-success">Done</span></td>
												<td class="d-none d-md-table-cell">William Harris</td>
											</tr>
											<tr>
												<td>Project X</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-success">Done</span></td>
												<td class="d-none d-md-table-cell">Sharon Lessman</td>
											</tr>
											<tr>
												<td>Project Romeo</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-success">Done</span></td>
												<td class="d-none d-md-table-cell">Christina Mason</td>
											</tr>
											<tr>
												<td>Project Wombat</td>
												<td class="d-none d-xl-table-cell">01/01/2023</td>
												<td class="d-none d-xl-table-cell">31/06/2023</td>
												<td><span class="badge bg-warning">In progress</span></td>
												<td class="d-none d-md-table-cell">William Harris</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

			</main>
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-start">
							<p class="mb-0">
								<a class="text-muted" href="https://adminkit.io/"
									target="_blank"><strong>AdminKit</strong></a> - <a class="text-muted"
									href="https://adminkit.io/" target="_blank"><strong>Bootstrap Admin
										Template</strong></a> &copy;
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

	<!-- Chart.js CDN -->
	<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
	<script src="js/app.js"></script>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			<?php if (!empty($statusData)): ?>
			if (typeof Chart === 'undefined') {
				console.error('Chart.js is not loaded');
				return;
			}
			
			const statusData = <?= json_encode(array_values($statusData)) ?>;
			const statusLabels = statusData.map(item => item.status);
			const statusCounts = statusData.map(item => parseInt(item.total));
			
			const statusColors = {
				'open': '#3b82f6',
				'in-progress': '#f59e0b',
				'resolved': '#10b981',
				'closed': '#6b7280'
			};
			
			const backgroundColors = statusLabels.map(status => {
				const normalizedStatus = status.toLowerCase().replace(/\s+/g, '-');
				return statusColors[normalizedStatus] || '#8b5cf6';
			});
			
			const ctx = document.getElementById('agentStatusPieChart');
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


</html>

