#include "BodyBugList.h"

BodyBugList::BodyBugList(QWidget *parent) : QWidget(parent)
{
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
}

void BodyBugList::Show(BodyBugTracker *pageManager, QJsonObject UNUSED *data)
{
    _pageManager = pageManager;
    //TODO : Link API
    //Start Fake data
    QList<QPair<int, QString> > dataf;
    dataf.append(QPair<int, QString>(1, "L'api marche pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    dataf.append(QPair<int, QString>(1, "L'api marche pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    dataf.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    //End Fake data
    this->CreateList(dataf);
    emit OnLoadingDone(BodyBugTracker::BUGLIST);
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

void BodyBugList::CreateList(QList<QPair<int, QString> > &elemList)
{
    QList<QPair<int, QString> >::iterator listIt;

    for (listIt = elemList.begin(); listIt != elemList.end(); ++listIt)
    {
        BugListElement  *newElem = new BugListElement(_pageManager, (*listIt).second, (*listIt).first);

        //TODO : Connect elem signals to slots
        newElem->setFixedHeight(LIST_ELEM_HEIGHT);
        _listAdapter->addWidget(newElem);
    }
}

void BodyBugList::TriggerNewIssue()
{
    _pageManager->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGCREATE, NULL);
}
