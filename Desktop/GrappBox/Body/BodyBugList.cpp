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

void BodyBugList::Show(BodyBugTracker *pageManager, QJsonObject UNUSED *data)
{
    QVector<QString> data;

    _pageManager = pageManager;
    //TODO : Link API
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(-1); // TODO : Put current project id
    data.append(0);
    data.append(20);
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_XLAST_BUG_OFFSET, data, this, "OnGetBugListSuccess", "OnRequestFailure");
}

void BodyBugList::Hide()
{
    this->DeleteListElements();
    hide();
}

void BodyBugList::DeleteListElements()
{
    QLayoutItem *currentItem;

    while ((currentItem = _listAdapter->itemAt(0)) != NULL)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(NULL);
        _listAdapter->removeItem(currentItem);
    }
}

void BodyBugList::CreateList()
{
    QList<BugEntity>::iterator listIt;

    for (listIt = _bugList.begin(); listIt != _bugList.end(); ++listIt)
    {
        BugListElement  *newElem = new BugListElement(_pageManager, (*listIt).GetTitle(), (*listIt).GetID());

        newElem->setFixedHeight(LIST_ELEM_HEIGHT);
        _listAdapter->addWidget(newElem);
    }
}

void BodyBugList::TriggerNewIssue()
{
    _pageManager->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGCREATE, NULL);
}

//API Slots
void BodyBugList::OnGetBugListSuccess(int UNUSED id, QByteArray data)
{
    QList<QPair<int, QString> > dataf;
    QJsonDocument doc = QJsonDocument::fromRawData(data);
    QJsonObject json = doc.object();

    for (int i = 0; !json[i].isNull(); ++i)
        _bugList.append(BugEntity(json[i].toObject()));

    this->CreateList();
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
}

void BodyBugList::OnRequestFailure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, "Connexion to Grappbox server failed", "We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com");
}
