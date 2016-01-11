#include "BodyBugList.h"

BodyBugList::BodyBugList(QWidget *parent) : QWidget(parent)
{
    QString style;
    _mainLayout = new QVBoxLayout();
    _listAdapter = new QVBoxLayout();
    _title = new BugListTitleWidget();
    _listScrollView = new QScrollArea();

    _title->setFixedHeight(LIST_TITLE_HEIGHT);
    _listScrollView->setWidgetResizable(true);
    _listScrollView->setWidget(new QWidget());
    _listScrollView->widget()->setLayout(_listAdapter);

    QObject::connect(_title, SIGNAL(OnNewIssue()), this, SLOT(TriggerNewIssue()));
    QObject::connect(_title, SIGNAL(OnFilterStateChanged(BugListTitleWidget::BugState)), this, SLOT(TriggerFilterChange(BugListTitleWidget::BugState)));

    _mainLayout->addWidget(_title);
    _mainLayout->addWidget(_listScrollView);
    this->setLayout(_mainLayout);

    //Design
    _listAdapter->setMargin(0);
    _mainLayout->setMargin(0);
    style = "*{"
            "border : 1px;"
            "border-color : #3c3b3b;"
            "}"
            "BugListElement#Pair"
            "{"
            "background-color: #F6F6F6;"
            "}"
            "BugListElement#Odd"
            "{"
            "background-color: #F0F0F0;"
            "}";
    this->setStyleSheet(style);
}

void BodyBugList::Show(BodyBugTracker *pageManager, QJsonObject UNUSED *dataPage)
{
    QVector<QString> data;

    _pageManager = pageManager;

    //TODO : Link API
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    data.append(QString::number(1));
    data.append(QString::number(0));
    data.append(QString::number(20));
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_XLAST_BUG_OFFSET_BY_STATE, data, this, "OnGetBugListSuccess", "OnRequestFailure");
    data.clear();
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    data.append(QString::number(0));
    data.append(QString::number(20));
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_XLAST_BUG_OFFSET_CLOSED, data, this, "OnGetBugListClosedSuccess", "OnRequestFailure");
}

void BodyBugList::Hide()
{
    this->DeleteListElements();
    hide();
}

void BodyBugList::ClearLayout(QLayout *layout)
{
    QLayoutItem *item;

    while ((item = layout->takeAt(0)) != 0)
    {
        if (item->layout())
            ClearLayout(item->layout());
        else if (item->widget())
            delete item->widget();
        delete item;
    }
}

void BodyBugList::DeleteListElements()
{
    QWidget *newScrollArea = new QWidget();

    _listAdapter->destroyed();
    _listAdapter = new QVBoxLayout();
    newScrollArea->setLayout(_listAdapter);
    _listScrollView->setWidget(newScrollArea);
}

void BodyBugList::CreateList()
{
    QList<BugEntity>::iterator listIt;
    QList<BugEntity>::iterator begin = (_title->GetState() == BugListTitleWidget::OPEN ? _bugListOpen.begin() : _bugListClosed.begin());
    QList<BugEntity>::iterator end = (_title->GetState() == BugListTitleWidget::OPEN ? _bugListOpen.end() : _bugListClosed.end());

    for (listIt = begin; listIt != end; ++listIt)
    {
        BugListElement  *newElem = new BugListElement(_pageManager, (*listIt).GetTitle(), (*listIt).GetID());

        newElem->setFixedHeight(LIST_ELEM_HEIGHT);
        _listAdapter->addWidget(newElem);
        _listAdapter->setAlignment(newElem, Qt::AlignTop);
    }
    _listScrollView->repaint();
}

void BodyBugList::TriggerNewIssue()
{
    _pageManager->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGCREATE, NULL);
}

//API Slots
void BodyBugList::OnGetBugListClosedSuccess(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonArray tickets = json["tickets"].toArray();
    QJsonArray::iterator ticketIt = tickets.begin();

    for (ticketIt = tickets.begin(); ticketIt != tickets.end(); ticketIt++)
    {
        QJsonObject current = (*ticketIt).toObject();

        _bugListClosed.append(BugEntity(current));
    }
    if (_title->GetState() == BugListTitleWidget::CLOSED)
    {
        this->DeleteListElements();
        this->CreateList();
    }
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
}

void BodyBugList::OnGetBugListSuccess(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonArray tickets = json["tickets"].toArray();
    QJsonArray::iterator ticketIt = tickets.begin();

    for (ticketIt = tickets.begin(); ticketIt != tickets.end(); ticketIt++)
    {
        QJsonObject current = (*ticketIt).toObject();

        _bugListOpen.append(BugEntity(current));
    }
    if (_title->GetState() == BugListTitleWidget::OPEN)
    {
        this->DeleteListElements();
        this->CreateList();
    }
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
}

void BodyBugList::OnRequestFailure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, "Connexion to Grappbox server failed", "We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com");
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
}

void BodyBugList::TriggerFilterChange(BugListTitleWidget::BugState UNUSED state)
{
    this->DeleteListElements();
    this->CreateList();
}
