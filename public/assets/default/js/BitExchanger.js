$(document).ready(function() {
	bit_rates();
	bit_reserve();
	bit_get_gateway_image(1);
	bit_get_gateway_image(2);
});

function bit_exchange_step_1() {
	var login_to_exchange = $("#bit_login_to_exchange").val();
	var ses_uid = $("#bit_ses_uid").val();
	if(login_to_exchange == "1") {
		if(ses_uid > 0) {
			bit_exchange_step_2();
		} else {
			$("#login").modal("show");
			$("#bit_require_login").show();
		}
	} else {
		bit_exchange_step_2();
	}
}

function bit_exchange_step_2() {
	var url = $("#url").val();
	var data_url = url + "requests/bit_exchange_step_2.php";
	$.ajax({
		type: "POST",
		url: data_url,
		data: $("#bit_exchange_form").serialize(),
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#bit_exchange_box").html(data.msg);
			} else {
				$("#bit_exchange_results").html(data.msg);
			}
		}
	});
}

function bit_exchange_step_3() {
	var url = $("#url").val();
	var data_url = url + "requests/bit_exchange_step_3.php";
	$.ajax({
		type: "POST",
		url: data_url,
		data: $("#bit_exchange_form").serialize(),
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#bit_exchange_box").html(data.msg);
			} else {
				$("#bit_exchange_results").html(data.msg);
			}
		}
	});
}

function bit_make_exchange(id) {
	var url = $("#url").val();
	var data_url = url + "requests/bit_make_exchange.php?id="+id;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "html",
		success: function (data) {
			$("#bit_exchange_box").html(data);
		}
	});
}

function bit_cancel_exchange(id) {
	var url = $("#url").val();
	var data_url = url + "requests/bit_cancel_exchange.php?id="+id;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "html",
		success: function (data) {
			$("#bit_exchange_box").html(data);
		}
	});
}

function bit_confirm_transaction(id) {
	var url = $("#url").val();
	var data_url = url + "requests/bit_confirm_transaction.php?id="+id;
	$.ajax({
		type: "POST",
		url: data_url,
		data: $("#bit_confirm_transaction").serialize(),
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#bit_confirm_transaction").hide();
				$("#bit_transaction_results").html(data.msg);
			} else {
				$("#bit_transaction_results").html(data.msg);
			}
		}
	});
}

function bit_decode_company(value) {
	if(value == "Bitcoin") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else if(value == "Litecoin") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else if(value == "Dogecoin") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else if(value == "Dash") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else if(value == "Peercoin") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else if(value == "Ethereum") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	}  else if(value == "TheBillioncoin") {
		$("#bit_company").html(value);
		$("#bit_account").hide();
		$("#bit_address").show();
	} else {
		$("#bit_company").html(value);
		$("#bit_account").show();
		$("#bit_address").hide();
	}
}

function bit_rates() {
	var url = $("#url").val();
	var gateway_send = $("#bit_gateway_send").val();
	var gateway_receive = $("#bit_gateway_receive").val();
	var data_url = url + "requests/bit_rates.php?gateway_send="+gateway_send+"&gateway_receive="+gateway_receive;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				var ex_rate = data.rate_from+" "+data.currency_form+" = "+data.rate_to+" "+data.currency_to;
				$("#bit_exchange_rate").html(ex_rate);
				$("#bit_rate_from").val(data.rate_from);
				$("#bit_rate_to").val(data.rate_to);
				$("#bit_currency_from").val(data.currency_form);
				$("#bit_currency_to").val(data.currency_to);
				$("#bit_amount_send").val(data.rate_from);
				$("#bit_amount_receive").val(data.rate_to);
				$("#bit_amount_receive2").val(data.rate_to);
			} else {
				$("#bit_exchange_rate").html("-");
				$("#bit_amount_send").val("0");
				$("#bit_amount_receive").val("0");
				$("#bit_amount_receive2").val("0");
				$("#bit_rate_from").val("0");
				$("#bit_rate_to").val("0");
				$("#bit_currency_from").val("");
				$("#bit_currency_to").val("");
			}
		}
	});
}

function bit_reserve() {
	var url = $("#url").val();
	var gateway_send = $("#bit_gateway_send").val();
	var gateway_receive = $("#bit_gateway_receive").val();
	var data_url = url + "requests/bit_reserve.php?gateway_send="+gateway_send+"&gateway_receive="+gateway_receive;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "html",
		success: function (data) {
			$("#bit_reserve").html(data);
		}
	});
}

function isCrypto(name) {
	if(name == "BTC") { return true; }
	else if(name == "LTC") { return true; }
	else if(name == "TBC") { return true; }
	else if(name == "DASH") { return true; }
	else if(name == "DOGE") { return true; }
	else if(name == "PPC") { return true; }
	else if(name == "TBC") { return true; }
	else {
		return false;
	}
}

function bit_calculator() {
	var currency_from = $("#bit_currency_from").val();
	var currency_to = $("#bit_currency_to").val();
	var rate_from = $("#bit_rate_from").val();
	var rate_to = $("#bit_rate_to").val();
	var amount_send = $("#bit_amount_send").val();
	if(isNaN(amount_send)) {
		var data = '0';
	} else {
		if(isCrypto(currency_from) && isCrypto(currency_to)) {
			var sum = amount_send * rate_to;
			var data = sum.toFixed(6);
		} else if(isCrypto(currency_to)) {
			var sum = amount_send / rate_from;
			var data = sum.toFixed(6);
		} else if(rate_from > 1) {
			var sum = amount_send / rate_from;
			var data = sum.toFixed(2);
		} else {
			var sum = amount_send * rate_to;
			var data = sum.toFixed(2);
		}	
			//var sum = amount_send / rate_from;
			//var data = sum.toFixed(8);
			//var sum = amount_send * rate_to;
			//var data = sum.toFixed(2);
	}
	$("#bit_amount_receive").val(data);
	$("#bit_amount_receive2").val(data);
}

function bit_refresh(type) {
	bit_rates();
	bit_reserve();
	bit_get_gateway_image(type);
}

function bit_get_gateway_image(type) {
	var url = $("#url").val();
	if(type == "1") {
		var gateway_id = $("#bit_gateway_send").val();
	} else if(type == "2") {
		var gateway_id = $("#bit_gateway_receive").val();
	} else { }
	var data_url = url + "requests/bit_get_gateway_image.php?gateway_id="+gateway_id;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "html",
		success: function (data) {
			if(type == "1") {
				$("#bit_image_send").attr("src", data);
			} else if(type == "2") {
				$("#bit_image_receive").attr("src", data);
			} else { }
		}
	});
}

function bit_login() {
	var url = $("#url").val();	
	var data_url = url + "requests/bit_login.php";
	$.ajax({
		type: "POST",
		url: data_url,
		data: $("#login_form").serialize(),
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#login_form").hide();
				$("#login_results").html(data.msg);
			} else {
				$("#login_results").html(data.msg);
			}
		}
	});
}

function bit_register() {
	var url = $("#url").val();	
	var data_url = url + "requests/bit_register.php";
	$.ajax({
		type: "POST",
		url: data_url,
		data: $("#register_form").serialize(),
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#register_form").hide();
				$("#register_results").html(data.msg);
			} else {
				$("#register_results").html(data.msg);
			}
		}
	});
}

function bit_l_acc_fields(val) {
	var url = $("#url").val();	
	var wallet_id = $("#wallet_id").val();
	bit_get_wallet_exchange_rate(val,wallet_id);
	var data_url = url + "requests/load_account_fields.php?gateway="+val;
				$.ajax({
					type: "GET",
					url: data_url,
					dataType: "html",
					success: function (data) {
						$("#account_fields").html(data);
					}
				});
}

function bit_get_wallet_exchange_rate(gateway_id,wallet_id) {
	var url = $("#url").val();	
	var data_url = url + "requests/bit_get_wallet_exchange_rate.php?gateway_id="+gateway_id+"&wallet_id="+wallet_id;
	$.ajax({
		type: "GET",
		url: data_url,
		dataType: "json",
		success: function (data) {
			if(data.status == "success") {
				$("#rate_from").val(data.rate_from);
				$("#rate_to").val(data.rate_to);
				$("#currency_from").val(data.currency_form);
				$("#currency_to").val(data.currency_to);
				$("#bit_rate_from").html(data.rate_from);
				$("#bit_rate_to").html(data.rate_to);
				$("#bit_currency_from").html(data.currency_form);
				$("#bit_currency_to").html(data.currency_to);
				$("#bitexc_exchange_rate").show();
			} else {
				$("#rate_from").val(data.rate_from);
				$("#rate_to").val(data.rate_to);
				$("#currency_from").val(data.currency_form);
				$("#currency_to").val(data.currency_to);
				$("#bitexc_exchange_rate").hide();
			}
		}
	});
}

function bit_exch_cal(val) {
	var currency_from = $("#currency_from").val();
	var currency_to = $("#currency_to").val();
	var rate_from = $("#rate_from").val();
	var rate_to = $("#rate_to").val();
	var amount_send = val;
	if(isNaN(amount_send)) {
		var data = '0';
	} else {
		if(isCrypto(currency_from) && isCrypto(currency_to)) {
			var sum = amount_send * rate_to;
			var data = sum.toFixed(6);
		} else if(isCrypto(currency_to)) {
			var sum = amount_send / rate_from;
			var data = sum.toFixed(6);
		} else if(rate_from > 1) {
			var sum = amount_send / rate_from;
			var data = sum.toFixed(2);
		} else {
			var sum = amount_send * rate_to;
			var data = sum.toFixed(2);
		}	
			//var sum = amount_send / rate_from;
			//var data = sum.toFixed(8);
			//var sum = amount_send * rate_to;
			//var data = sum.toFixed(2);
	}
	$("#amount_receive").val(data);
	$("#amount_receive2").val(data);
}
