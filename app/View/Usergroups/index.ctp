<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.
?>
<?php $this->Paginator->options(array('url' => $this->params['named'])); ?>
<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-users fa-fw "></i>
				<?php echo __('Administration'); ?>
			<span>>
				<?php echo __('Manage user roles'); ?>
			</span>
		</h1>
	</div>
</div>
<section id="widget-grid" class="">
	<div class="row">

		<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false" >
				<header>
					<div class="widget-toolbar" role="menu">
						<?php
						if($this->Acl->hasPermission('add')):
							echo $this->Html->link(__('New'),
								'/'.$this->params['controller'].'/add', [
								'class' => 'btn btn-xs btn-success',
								'icon' => 'fa fa-plus'
							]);
						endif;
						?>
						</div>
						<div class="widget-toolbar" role="menu">
						<a href="javascript:void(0);" class="dropdown-toggle selector" data-toggle="dropdown"><i class="fa fa-lg fa-table"></i></a>
						<ul class="dropdown-menu arrow-box-up-right pull-right">
							<li style="width: 100%;"><a href="javascript:void(0)" class="select_datatable text-left" class="select_datatable text-left" my-column="1"><input type="checkbox" class="pull-left" />&nbsp; <?php echo __('Name'); ?></a></li>
							<li style="width: 100%;"><a href="javascript:void(0)" class="select_datatable text-left" class="select_datatable text-left" my-column="2"><input type="checkbox" class="pull-left" />&nbsp; <?php echo __('Description'); ?></a></li>
						</ul>
						<div class="clearfix"></div>
					</div>
					<div class="jarviswidget-ctrls" role="menu">
					</div>
					<span class="widget-icon hidden-mobile"> <i class="fa fa-users"></i> </span>
					<h2 class="hidden-mobile"><?php echo __('User role'); ?></h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						<div class="mobile_table">
							<table id="timeperiod_list" class="table table-striped table-bordered smart-form" style="">
								<thead>
									<tr>
										<?php $order = $this->Paginator->param('order'); ?>
										<th class="select_datatable no-sort"><?php echo $this->Utils->getDirection($order, 'Usergroup.name'); echo $this->Paginator->sort('Usergroup.name', 'Name'); ?></th>
										<th class="no-sort"><?php echo $this->Utils->getDirection($order, 'Usergroup.description'); echo $this->Paginator->sort('Usergroup.description', 'Description'); ?></th>
										<th class="no-sort"></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($usergroups as $usergroup): ?>
										<tr>
											<td><?php echo $usergroup['Usergroup']['name']; ?></td>
											<td><?php echo $usergroup['Usergroup']['description']; ?></td>
											</td>
											<td>
												<div class="btn-group">
														<?php if($this->Acl->hasPermission('edit')): ?>
															<a href="/<?php echo $this->params['controller']; ?>/edit/<?php echo $usergroup['Usergroup']['id']; ?>" class="btn btn-default">&nbsp;<i class="fa fa-cog"></i>&nbsp;</a>
														<?php else:?>
															<a href="javascript:void(0);" class="btn btn-default">&nbsp;<i class="fa fa-cog"></i>&nbsp;</a>
														<?php endif;?>
														<a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-default dropdown-toggle"><span class="caret"></span></a>
														<ul class="dropdown-menu">
															<?php if($this->Acl->hasPermission('edit')): ?>
																<li>
																	<a href="/<?php echo $this->params['controller']; ?>/edit/<?php echo $usergroup['Usergroup']['id']; ?>"><i class="fa fa-cog"></i> <?php echo __('Edit'); ?></a>
																</li>
															<?php endif;?>
															<?php if($this->Acl->hasPermission('delete')): ?>
																<li class="divider"></li>
																<li>
																	<?php echo $this->Form->postLink('<i class="fa fa-trash-o"></i> '.__('Delete'), ['controller' => $this->params['controller'], 'action' => 'delete', $usergroup['Usergroup']['id']], ['class' => 'txt-color-red', 'escape' => false]); ?>
																</li>
															<?php endif;?>
														</ul>
													</div>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<?php if(empty($usergroups)):?>
							<div class="noMatch">
								<center>
									<span class="txt-color-red italic"><?php echo __('search.noVal'); ?></span>
								</center>
							</div>
						<?php endif;?>
						<div style="padding: 5px 10px;">
							<div class="row">
								<div class="col-sm-6">
									<div class="dataTables_info" style="line-height: 32px;" id="datatable_fixed_column_info"><?php echo $this->Paginator->counter(__('paginator.showing').' {:page} '.__('of').' {:pages}, '.__('paginator.overall').' {:count} '.__('entries')); ?></div>
								</div>
								<div class="col-sm-6 text-right">
									<div class="dataTables_paginate paging_bootstrap">
										<?php echo $this->Paginator->pagination(array(
											'ul' => 'pagination'
										)); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</section>


