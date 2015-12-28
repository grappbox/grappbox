#include "BugListElement.h"

BugListElement::BugListElement(BodyBugTracker *pageManager, const QString &bugTitle, const int bugId, QWidget *parent) : QWidget(parent)
{
    _pageManager = pageManager;
    _title = new QLabel(bugTitle);
    _mainLayout = new QHBoxLayout();
    _btnViewBug = new QPushButton(tr("View"));
    _btnCloseBug = new QPushButton(tr("Close"));
    _bugID = bugId;

    QObject::connect(_btnViewBug, SIGNAL(released()), this, SLOT(TriggerBtnView()));
    QObject::connect(_btnCloseBug, SIGNAL(released()), this, SLOT(TriggerBtnClose()));

    _mainLayout->addWidget(_title);
    _mainLayout->addWidget(_btnViewBug);
    _mainLayout->addWidget(_btnCloseBug);
    this->setLayout(_mainLayout);
}

void BugListElement::TriggerBtnView()
{
    QJsonObject *data = new QJsonObject();
    data->insert("id", _bugID);
    data->insert("title", _title->text());
    _pageManager->TriggerChangePage(BodyBugTracker::BUGVIEW, data);
    emit OnViewBug(_bugID);
}

void BugListElement::TriggerBtnClose()
{
    emit OnCloseBug(_bugID);
}
