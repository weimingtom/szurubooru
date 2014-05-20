<?php
class EditUserJobTest extends AbstractTest
{
	public function testSaving()
	{
		$this->grantAccess('editUserName.own');
		$this->grantAccess('editUserPassword.own');
		$user = $this->userMocker->mockSingle();

		$newName = 'dummy' . uniqid();

		$user = $this->assert->doesNotThrow(function() use ($user, $newName)
		{
			return Api::run(
				new EditUserJob(),
				[
					JobArgs::ARG_USER_NAME => $user->getName(),
					JobArgs::ARG_NEW_USER_NAME => $newName,
					JobArgs::ARG_NEW_PASSWORD => 'changed',
				]);
		});

		//first user = admin
		$this->assert->areEqual($newName, $user->getName());
		$this->assert->areEquivalent(new AccessRank(AccessRank::Registered), $user->getAccessRank());
		$this->assert->isFalse(empty($user->getPasswordSalt()));
		$this->assert->isFalse(empty($user->getPasswordHash()));
	}

	public function testPartialPrivilegeFail()
	{
		$this->grantAccess('editUserName.own');
		$user = $this->userMocker->mockSingle();

		$newName = 'dummy' . uniqid();

		$this->assert->throws(function() use ($user, $newName)
		{
			return Api::run(
				new EditUserJob(),
				[
					JobArgs::ARG_USER_NAME => $user->getName(),
					JobArgs::ARG_NEW_USER_NAME => $newName,
					JobArgs::ARG_NEW_PASSWORD => 'this should make it fail',
				]);
		}, 'Insufficient privilege');
	}

	public function testLogBuffering()
	{
		$this->testSaving();

		$logPath = Logger::getLogPath();
		$x = file_get_contents($logPath);
		$lines = explode("\n", $x);
		$this->assert->areEqual(2, count($lines));
	}

	public function testCanEditSomething()
	{
		$this->grantAccess('editUserName.own');
		$user = $this->userMocker->mockSingle();
		$user = $this->assert->isTrue((new EditUserJob())->canEditAnything($user));
	}

	public function testCannotEditAnything()
	{
		$user = $this->userMocker->mockSingle();
		$user = $this->assert->isFalse((new EditUserJob())->canEditAnything($user));
	}
}