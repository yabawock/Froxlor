<div class="messagewrapper">
	<form action="{$yeslink}" method="post">
		<div class="warningcontainer bradius">
			<div class="warningtitle">{t}Question{/t}</div>
			<div class="warning">
				{$text}
				<div style="text-align:right;margin-top:10px;">
					<input type="hidden" name="send" value="send" />
					{$hiddenparams}
					<input type="submit" name="submitbutton" value="{t}Yes{/t}" />&nbsp;
					<input type="button" class="nobutton" value="{t}No{/t}" id="yesnobutton" />
				</div>
			</div>
		</div>
	</form>
</div>
