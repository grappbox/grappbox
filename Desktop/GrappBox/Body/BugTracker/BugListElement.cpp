#include "BugListElement.h"

bool BugListElement::_pair = true;

BugListElement::BugListElement(BodyBugTracker *pageManager, const QString &bugTitle, const int bugId, QWidget *parent) : QWidget(parent)
{
    QString style;
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


    // [STYLE]
    _mainLayout->setMargin(0);
    _btnViewBug->setObjectName("Open");
    _btnCloseBug->setObjectName("Close");
    if (BugListElement::_pair)
        this->setObjectName("Pair");
    else
        this->setObjectName("Odd");
    style = "*{"
            "padding : 2px;"
            "}";
    style += "QPushButton#Open {"
             "background-color : #70ad47;"
             "color : #ffffff;"
             "border-radius: 2px;"
             "padding : 0px;"
             "padding-top: 5px;"
             "padding-bottom: 5px;"
             "font-size : 15px;"
             "max-width : 150px;"
             "max-height : 75px;"
             "}"
             "QPushButton#Close {"
             "background-color : #c0392b;"
             "color : #ffffff;"
             "border-radius: 2px;"
             "padding : 0px;"
             "padding-top: 5px;"
             "padding-bottom: 5px;"
             "font-size : 15px;"
             "max-width : 150px;"
             "max-height : 75px;"
             "margin-right : 10px;"
             "}"
             "QPushButton#Open:hover"
             "{"
             "background-color: #9cbc85;"
             "}"
             "QPushButton#Close:hover"
             "{"
             "background-color: #d36c63;"
             "}"
             "QLabel"
             "{"
             "font-weight: bold;"
             "font-size: 15px;"
             "padding-top: 5px;"
             "padding-bottom: 5px;"
             "}";
    this->setStyleSheet(style);
    this->ensurePolished();
    BugListElement::_pair = !BugListElement::_pair;
}

void BugListElement::TriggerBtnView()
{
    QJsonObject *data = new QJsonObject();

    data->insert("id", _bugID);
    data->insert("title", _title->text());
    _pageManager->TriggerChangePage(BodyBugTracker::BUGVIEW, data);
}

void BugListElement::TriggerBtnClose()
{
    emit OnCloseBug(_bugID);
}
