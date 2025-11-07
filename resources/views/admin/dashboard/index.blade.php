@extends('admin.layouts.main-layout')


@section('title', 'Dashboard')

@section('style')
<style>
.custom-bg {
    background-color: #ecf1ff !important;
}

.filter-btnbtn-primary:not(:disabled):not(.disabled):active,
.filter-btn .thm-btn-bg thm-btn-text-color:not(:disabled):not(.disabled).active {
    color: #fff;
    background-color: #21409a;
    border-color: #000000;
}

.filter-button-group{
    background-color: rgb(94 29 102 / 20%);
}
.filter-button-group .btn.active{
    border: 0px;
    background-color: #5e1d66;
    color: #fff;
}
.btn:focus, .btn.focus {
    outline: none !important;
    box-shadow: none !important;
}

.pie-chart-card-header{
    background-image: linear-gradient(270deg, #5e1d66 10%, #3a4973 100%);
}

</style>
@endsection

@section('content')
<div class="view-container mb-2">

    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Dashboard</h3>
            <div class="d-flex gap-2">
                <div class="btn-group btn-group-toggle flex-wrap filter-button-group" data-toggle="buttons">
                    <label class="btn active btn-sm">
                        <input type="radio" name="options" id="filter_today" autocomplete="off" checked> Today
                    </label>
                    <label class="btn btn-sm">
                        <input type="radio" name="options" id="filter_yesterday" autocomplete="off"> Yesterday
                    </label>
                    <label class="btn btn-sm">
                        <input type="radio" name="options" id="filter_thisWeek" autocomplete="off"> This Week
                    </label>
                    <label class="btn btn-sm">
                        <input type="radio" name="options" id="filter_lastWeek" autocomplete="off"> Last Week
                    </label>
                    <label class="btn btn-sm">
                        <input type="radio" name="options" id="filter_thisMonth" autocomplete="off"> This Month
                    </label>
                    <label class="btn btn-sm">
                        <input type="radio" name="options" id="filter_lastMonth" autocomplete="off"> Last Month
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body p-1">

            <div class="row g-3">
                <!-- Total Expense -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted  mb-2">Total Expense</h6>
                                <h3 id="totalExpense" class="mb-0">TK. ⏳</h3>
                            </div>
                            <div class="text-danger fs-2">
                                <i class="bi bi-credit-card-2-back"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted mb-2">Total Customers</h6>
                                <h3 id="totalCustomers" class="mb-0">⏳</h3>
                            </div>
                            <div class="text-info fs-2">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Customer -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted mb-2">Total Number of Sales</h6>
                                <h3 id="totalNumberOfSales" class="mb-0">⏳</h3>
                            </div>
                            <div class="text-success fs-2">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sales Amount -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted mb-2">Total Sales Amount</h6>
                                <h3 id="totalSalesAmount" class="mb-0">TK. ⏳</h3>
                            </div>
                            <div class="text-primary fs-2">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Discount Amount -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted mb-2">Total Discount Amount</h6>
                                <h3 id="discountAmount" class="mb-0">TK. ⏳</h3>
                            </div>
                            <div class="text-primary fs-2">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Discount Amount -->
                <div class="col-md-4 col-lg-2">
                    <div class="card shadow-sm py-2 custom-bg">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-subtitle text-muted mb-2">Total Adjusted Amount</h6>
                                <h3 id="adjustmentAmount" class="mb-0">TK. ⏳</h3>
                            </div>
                            <div class="text-primary fs-2">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-2">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center text-white pie-chart-card-header">
                            <h5 class="mb-0">Total Sales Amount</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentTypePieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center text-white pie-chart-card-header">
                            <h5 class="mb-0">Wallet Payment Amount</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="walletPaymentPieChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center text-white pie-chart-card-header">
                            <h5 class="mb-0">Customer (New vs Returning)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="customerDistributionPieChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Top 5 Tables Section -->
            <div class="row mt-3">
                <!-- Top 5 Services -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center text-white pie-chart-card-header">
                            <h5 class="mb-0">Top 8 Services</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Service Name</th>
                                            <th class="border-0 text-end">Total Sales Count</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topServicesTable">
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">⏳ Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('script')

<script>
$(document).ready(function() {

    $('input[name="options"]').on('change', function() {
        const filterValue = $(this).attr('id').replace('filter_', '');
    });

});
</script>
@endsection