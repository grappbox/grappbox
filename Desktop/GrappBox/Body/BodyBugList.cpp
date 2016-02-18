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

    // [STYLE]
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

void BodyBugList::Show(BodyBugTracker *pageManager, QJsonObject  *dataPage)
{
    QVector<QString> data;

    _pageManager = pageManager;

    _bugListOpen.clear();
    _bugListClosed.clear();

	BEGIN_REQUEST;
	{
		SET_ON_DONE("OnGetBugListSuccess");
		SET_ON_FAIL("OnRequestFailure");
		SET_CALL_OBJECT(this);
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetToken());
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetCurrentProject());
		ADD_URL_FIELD(1);
		ADD_URL_FIELD(0);
		ADD_URL_FIELD(std::numeric_limits<int>::max());
		GET(API::DP_BUGTRACKER, API::GR_XLAST_BUG_OFFSET_BY_STATE);
	}
	END_REQUEST;
	
	BEGIN_REQUEST;
	{
		SET_ON_DONE("OnGetBugListClosedSuccess");
		SET_ON_FAIL("OnRequestFailure");
		SET_CALL_OBJECT(this);
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetToken());
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetCurrentProject());
		ADD_URL_FIELD(0);
		ADD_URL_FIELD(std::numeric_limits<int>::max());
		GET(API::DP_BUGTRACKER, API::GR_XLAST_BUG_OFFSET_CLOSED);
	}
	END_REQUEST;
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
        {
            item->widget()->disconnect();
            delete item->widget();
        }
        delete item;
    }
}

void BodyBugList::DeleteListElements()
{
    QWidget *newScrollArea = new QWidget();

    ClearLayout(_listAdapter);
    _listAdapter->deleteLater();
    _listAdapter = new QVBoxLayout();
    newScrollArea->setLayout(_listAdapter);
    _listScrollView->setWidget(newScrollArea);

}

void BodyBugList::CreateList()
{
    QList<BugEntity>::iterator listIt;
    QList<BugEntity>::iterator begin = (_title->GetState() == BugListTitleWidget::OPEN ? _bugListOpen.begin() : _bugListClosed.begin());
    QList<BugEntity>::iterator end = (_title->GetState() == BugListTitleWidget::OPEN ? _bugListOpen.end() : _bugListClosed.end());

    _listAdapter->setAlignment(Qt::AlignTop);
    for (listIt = begin; listIt != end; ++listIt)
    {
        BugListElement  *newElem = new BugListElement(_pageManager, (*listIt).GetTitle(), (*listIt).GetID());

        newElem->setFixedHeight(LIST_ELEM_HEIGHT);
        _listAdapter->addWidget(newElem);
        QObject::connect(newElem, SIGNAL(OnCloseBug(int)), this, SLOT(TriggerCloseBug(int)));
        _listAdapter->setAlignment(newElem, Qt::AlignTop);
    }
    _listScrollView->repaint();
}

void BodyBugList::TriggerNewIssue()
{
    _pageManager->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGCREATE, nullptr);
}

//API Slots
void BodyBugList::OnGetBugListClosedSuccess(int  id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object()["data"].toObject();
    QJsonArray tickets = json["array"].toArray();
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

void BodyBugList::OnGetBugListSuccess(int  id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
	QJsonObject json = doc.object()["data"].toObject();
    QJsonArray tickets = json["array"].toArray();
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

void BodyBugList::OnRequestFailure(int  id, QByteArray  data)
{
    QMessageBox::critical(this, "Connexion to Grappbox server failed", "We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com");
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
}

void BodyBugList::TriggerFilterChange(BugListTitleWidget::BugState  state)
{
    this->DeleteListElements();
    this->CreateList();
}

void BodyBugList::TriggerCloseBug(int bugId)
{
    int APIID;

	BEGIN_REQUEST;
	{
		SET_ON_DONE("TriggerCloseSuccess");
		SET_ON_FAIL("OnRequestFailure");
		SET_CALL_OBJECT(this);
		ADD_URL_FIELD(API::SDataManager::GetDataManager()->GetToken());
		ADD_URL_FIELD(bugId);
		APIID = DELETE(API::DP_BUGTRACKER, API::DR_CLOSE_TICKET_OR_COMMENT);
	}
	END_REQUEST;
    _waitingAPIIDBugId[APIID] = bugId;
}

void BodyBugList::TriggerCloseSuccess(int id, QByteArray  data)
{
    int bugId = _waitingAPIIDBugId[id];
    QList<BugEntity>::iterator bugIt;
    BugEntity entity = BugEntity();

    for (bugIt = _bugListOpen.begin(); bugIt != _bugListOpen.end(); ++bugIt)
    {
        if ((*bugIt).GetID() == bugId)
        {
            entity = (*bugIt);
            break;
        }
    }
    if (!entity.IsValid())
        return;
    _bugListClosed.append(entity);
    _bugListOpen.removeOne(entity);
    _waitingAPIIDBugId.remove(id);
    TriggerFilterChange(BugListTitleWidget::BugState::NONE);
}
