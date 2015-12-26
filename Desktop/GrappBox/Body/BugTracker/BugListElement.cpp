#include "BugListElement.h"

BugListElement::BugListElement(const QString &bugTitle, const int bugId, QWidget *parent) : QWidget(parent)
{
    QLabel *lblBugTitle = new QLabel(bugTitle);
    _mainLayout = new QHBoxLayout();
    _btnViewBug = new QPushButton(tr("View"));
    _btnCloseBug = new QPushButton(tr("Close"));
    _bugID = bugId;

    QObject::connect(_btnViewBug, SIGNAL(released()), this, SLOT(TriggerBtnView()));
    QObject::connect(_btnCloseBug, SIGNAL(released()), this, SLOT(TriggerBtnClose()));

    _mainLayout->addWidget(lblBugTitle);
    _mainLayout->addWidget(_btnViewBug);
    _mainLayout->addWidget(_btnCloseBug);
    this->setLayout(_mainLayout);
}

void BugListElement::TriggerBtnView()
{
    emit OnViewBug(_bugID);
}

void BugListElement::TriggerBtnClose()
{
    emit OnCloseBug(_bugID);
}
