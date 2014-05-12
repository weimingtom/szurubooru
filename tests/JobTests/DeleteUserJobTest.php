<?php
class DeleteUserJobTest extends AbstractTest
{
	public function testRemoval()
	{
		$user = $this->mockUser();
		$this->login($user);
		$this->grantAccess('deleteUser');

		$this->assert->doesNotThrow(function() use ($user)
		{
			Api::run(
				new DeleteUserJob(),
				[
					JobArgs::ARG_USER_NAME => $user->getName(),
				]);
		});

		$this->assert->areEqual(null, UserModel::tryGetByName($user->getName()));
		$this->assert->areEqual(0, UserModel::getCount());
	}

	public function testWrongUserId()
	{
		$user = $this->mockUser();
		$this->login($user);

		$this->assert->throws(function()
		{
			Api::run(
				new DeleteUserJob(),
				[
					JobArgs::ARG_USER_NAME => 'robocop',
				]);
		}, 'Invalid user name');
	}
}
