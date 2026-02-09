<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
<<<<<<< HEAD
<<<<<<< HEAD
 include_once __DIR__ . '/compents/agentHeader.php'; 

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
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	

			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3"><strong>Analytics</strong> Dashboard</h1>

					<div class="row">
						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Sales</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="truck"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">2.382</h1>
												<div class="mb-0">
													<span class="text-danger"> <i
															class="mdi mdi-arrow-bottom-right"></i> -3.65% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Visitors</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="users"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">14.212</h1>
												<div class="mb-0">
													<span class="text-success"> <i
															class="mdi mdi-arrow-bottom-right"></i> 5.25% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Earnings</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="dollar-sign"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">$21.300</h1>
												<div class="mb-0">
													<span class="text-success"> <i
															class="mdi mdi-arrow-bottom-right"></i> 6.65% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Orders</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="shopping-cart"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">64</h1>
												<div class="mb-0">
													<span class="text-danger"> <i
															class="mdi mdi-arrow-bottom-right"></i> -2.25% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-xxl-7">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">Recent Movement</h5>
								</div>
								<div class="card-body py-3">
									<div class="chart chart-sm">
										<canvas id="chartjs-dashboard-line"></canvas>
									</div>
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

			<footer class="footer">
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

	<script src="js/app.js"></script>


</body>

</html>
=======
=======
 include_once __DIR__ . '/compents/agentHeader.php'; 
>>>>>>> c8ab191 (added agent dashboard and agent ticketpage)

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

<<<<<<< HEAD
$agent_id = $user_id; // the logged-in agent ID
>>>>>>> 6954315 (worked on user verification and ticket submittion by the user)
=======
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	

			<main class="content">
				<div class="container-fluid p-0">

					<h1 class="h3 mb-3"><strong>Analytics</strong> Dashboard</h1>

					<div class="row">
						<div class="col-xl-6 col-xxl-5 d-flex">
							<div class="w-100">
								<div class="row">
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Sales</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="truck"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">2.382</h1>
												<div class="mb-0">
													<span class="text-danger"> <i
															class="mdi mdi-arrow-bottom-right"></i> -3.65% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Visitors</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="users"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">14.212</h1>
												<div class="mb-0">
													<span class="text-success"> <i
															class="mdi mdi-arrow-bottom-right"></i> 5.25% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Earnings</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="dollar-sign"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">$21.300</h1>
												<div class="mb-0">
													<span class="text-success"> <i
															class="mdi mdi-arrow-bottom-right"></i> 6.65% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col mt-0">
														<h5 class="card-title">Orders</h5>
													</div>

													<div class="col-auto">
														<div class="stat text-primary">
															<i class="align-middle" data-feather="shopping-cart"></i>
														</div>
													</div>
												</div>
												<h1 class="mt-1 mb-3">64</h1>
												<div class="mb-0">
													<span class="text-danger"> <i
															class="mdi mdi-arrow-bottom-right"></i> -2.25% </span>
													<span class="text-muted">Since last week</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-xxl-7">
							<div class="card flex-fill w-100">
								<div class="card-header">

									<h5 class="card-title mb-0">Recent Movement</h5>
								</div>
								<div class="card-body py-3">
									<div class="chart chart-sm">
										<canvas id="chartjs-dashboard-line"></canvas>
									</div>
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

			<footer class="footer">
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

	<script src="js/app.js"></script>


</body>

</html>
>>>>>>> c8ab191 (added agent dashboard and agent ticketpage)
