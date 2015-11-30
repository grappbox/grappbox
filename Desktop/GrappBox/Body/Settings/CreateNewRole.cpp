#include "CreateNewRole.h"

CreateNewRole::CreateNewRole(QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QVBoxLayout(this);
    _accessControlLayout = new QHBoxLayout();
    _nameForm = new QFormLayout();
    _okForm = new QFormLayout();
    _formCheckboxes = new QFormLayout();
    _formCheckboxesMiddle = new QFormLayout();
    _timelineTeam = new QCheckBox();
    _timelineCustomer = new QCheckBox();
    _gantt = new QCheckBox();
    _whiteboard = new QCheckBox();
    _bugtracker = new QCheckBox();
    _event = new QCheckBox();
    _task = new QCheckBox();
    _projectSettings = new QCheckBox();
    _cloud = new QCheckBox();
    _name = new QLineEdit();
    _btnOK = new QPushButton(tr("OK"));
    _btnCancel = new QPushButton(tr("Cancel"));

    _nameForm->addRow(new QLabel(tr("Role name : ")), _name);
    _okForm->addRow(_btnOK, _btnCancel);


    _formCheckboxes->addRow(new QLabel(tr("Timeline Team : ")), _timelineTeam);
    _formCheckboxes->addRow(new QLabel(tr("Timeline Customer : ")), _timelineCustomer);
    _formCheckboxes->addRow(new QLabel(tr("Gantt : ")), _gantt);
    _formCheckboxes->addRow(new QLabel(tr("Timeline Team : ")), _whiteboard);
    _formCheckboxes->addRow(new QLabel(tr("Cloud : ")), _cloud);
    _accessControlLayout->addLayout(_formCheckboxes);
    _formCheckboxesMiddle->addRow(new QLabel(tr("Bugtracker : ")), _bugtracker);
    _formCheckboxesMiddle->addRow(new QLabel(tr("Event schedule : ")), _event);
    _formCheckboxesMiddle->addRow(new QLabel(tr("Tasks : ")), _task);
    _formCheckboxesMiddle->addRow(new QLabel(tr("Project settings : ")), _projectSettings);
    _accessControlLayout->addLayout(_formCheckboxesMiddle);

    _mainLayout->addLayout(_nameForm);
    _mainLayout->addLayout(_accessControlLayout);
    _mainLayout->addLayout(_okForm);

    QObject::connect(_btnOK, SIGNAL(released()), this, SLOT(OkTriggered()));
    QObject::connect(_btnCancel, SIGNAL(released()), this, SLOT(close()));
}

const QMap<QString, bool> CreateNewRole::GetRoleAuthorizations()
{
    QMap<QString, bool> auth;

    auth["timelineTeam"] = _timelineTeam->isChecked();
    auth["timelineCustomer"] = _timelineCustomer->isChecked();
    auth["gantt"] = _gantt->isChecked();
    auth["whiteboard"] = _whiteboard->isChecked();
    auth["bugtracker"] = _bugtracker->isChecked();
    auth["event"] = _event->isChecked();
    auth["task"] = _task->isChecked();
    auth["projectSettings"] = _projectSettings->isChecked();
    auth["cloud"] = _cloud->isChecked();
    return (auth);
}

const QString CreateNewRole::GetRoleName()
{
    return (_name->text());
}

void CreateNewRole::Open()
{
    _timelineTeam->setChecked(false);
    _timelineCustomer->setChecked(false);
    _gantt->setChecked(false);
    _whiteboard->setChecked(false);
    _bugtracker->setChecked(false);
    _event->setChecked(false);
    _task->setChecked(false);
    _projectSettings->setChecked(false);
    _cloud->setChecked(false);
    _name->setText("");

    show();
}

void CreateNewRole::OkTriggered()
{
    emit RoleConfirmed();
    this->close();
}
