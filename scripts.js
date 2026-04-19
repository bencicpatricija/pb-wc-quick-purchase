// Display a warning message when the quick purchase button is clicked
document.addEventListener('DOMContentLoaded', () => {
	const buttons = document.querySelectorAll('.pb-wcqp-button');
	buttons.forEach(button => {
		button.addEventListener('click', () => {
			alert(pbWcQuickPurchaseParams.confirmMessage);
		});
	});
});
