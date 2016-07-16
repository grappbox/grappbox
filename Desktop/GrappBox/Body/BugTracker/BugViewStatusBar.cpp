#include "BugViewStatusBar.h"

BugViewStatusBar::BugViewStatusBar(QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QHBoxLayout();
    _lblBugStatus = new QLabel(tr("Loading..."));
    _lblCreatorName = new QLabel(PH_BUGCREATORNAME + " " + _creatorName);
    _lblCreationDate = new QLabel(tr("Loading..."));

    _mainLayout->addWidget(_lblBugStatus);
    _mainLayout->addWidget(_lblCreatorName);
    _mainLayout->addWidget(_lblCreationDate);
    this->setLayout(_mainLayout);
}

void BugViewStatusBar::SetCreatorName(const QString &name)
{
    _creatorName = name;
    _lblCreatorName->setText(PH_BUGCREATORNAME + " " + _creatorName);
}

void BugViewStatusBar::SetBugStatus(const BugState state)
{
    _bugState = state;
    if (_bugState == BugState::NONE)
        return;
    _lblBugStatus->setText(_bugState == BugState::OPEN ? PH_BUGOPENSTATE : PH_BUGCLOSEDSTATE);
}

void BugViewStatusBar::SetDate(const QDateTime date)
{
    _lblCreationDate->setText(date.toString("yyyy-MM-dd HH:mm:ss"));
}
