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

    _mainLayout->addWidget(_title);
    _mainLayout->addWidget(_listScrollView);
    this->setLayout(_mainLayout);
}

void BodyBugList::Show(int ID, MainWindow *mainApp)
{
    _bodyID = ID;
    _mainApp = mainApp;
    //TODO : Link API
    //Start Fake data
    QList<QPair<int, QString> > data;
    data.append(QPair<int, QString>(1, "L'api marche pas"));
    data.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    data.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    data.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    data.append(QPair<int, QString>(1, "L'api marche pas"));
    data.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    data.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    data.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    data.append(QPair<int, QString>(1, "L'api marche pas"));
    data.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    data.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    data.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    data.append(QPair<int, QString>(1, "L'api marche pas"));
    data.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    data.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    data.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    data.append(QPair<int, QString>(1, "L'api marche pas"));
    data.append(QPair<int, QString>(1, "L'api marche toujours pas"));
    data.append(QPair<int, QString>(1, "L'api marche définitivement pas"));
    data.append(QPair<int, QString>(1, "L'api marche paaaaas"));
    this->CreateList(data);
    //End Fake data

    emit OnLoadingDone(_bodyID);
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
        if (currentItem->widget() != NULL)
        {
            _listAdapter->widget()->setParent(NULL);
            delete _listAdapter->widget();
        }
        delete _listAdapter;
    }
}

void BodyBugList::CreateList(QList<QPair<int, QString> > &elemList)
{
    QList<QPair<int, QString> >::iterator listIt;

    for (listIt = elemList.begin(); listIt != elemList.end(); ++listIt)
    {
        BugListElement  *newElem = new BugListElement((*listIt).second, (*listIt).first);

        //TODO : Connect elem signals to slots
        newElem->setFixedHeight(LIST_ELEM_HEIGHT);
        _listAdapter->addWidget(newElem);
    }
}
