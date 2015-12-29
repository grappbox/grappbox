#include "BugListTitleWidget.h"

BugListTitleWidget::BugListTitleWidget(QWidget *parent) : QWidget(parent)
{
    QString style;
    QLabel *lblTitle = new QLabel("<h2>" + tr("Bug list") + "</h2>");
    _mainLayout = new QHBoxLayout();
    _btnOpenState = new QPushButton(tr("Open"));
    _btnClosedState = new QPushButton(tr("Closed"));
    _btnNewIssue = new QPushButton(tr("New issue"));

    _btnOpenState->setCheckable(true);
    _btnOpenState->setChecked(true);
    _btnClosedState->setCheckable(true);
    _filterState = BugState::OPEN;

    QObject::connect(_btnOpenState, SIGNAL(toggled(bool)), this, SLOT(triggerOpenStateButtonToogled(bool)));
    QObject::connect(_btnClosedState, SIGNAL(toggled(bool)), this, SLOT(triggerClosedStateButtonToogled(bool)));
    QObject::connect(_btnNewIssue, QPushButton::released, [=] { emit OnNewIssue(); });

    _mainLayout->addWidget(lblTitle);
    _mainLayout->addWidget(_btnOpenState);
    _mainLayout->addWidget(_btnClosedState);
    _mainLayout->addWidget(_btnNewIssue);
    this->setLayout(_mainLayout);

    //Design
    _mainLayout->setMargin(0);
    _btnOpenState->setObjectName("OpenFilter");
    _btnClosedState->setObjectName("CloseFilter");
    _btnNewIssue->setObjectName("New");
    style = "QPushButton#OpenFilter{"
            "background-color: #595959;"
            "color : #ffffff;"
            "border-radius: 2px;"
            "max-width : 150px;"
            "max-height : 75px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#CloseFilter{"
            "background-color: #c0392b;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "max-width : 150px;"
            "max-height : 75px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#New"
            "{"
            "background-color: #70ad47;"
            "color: #ffffff;"
            "border-radius: 2px;"
            "max-width : 150px;"
            "max-height : 75px;"
            "font-size: 15px;"
            "font-weight: bold;"
            "}"
            "QPushButton#OpenFilter:checked, QPushButton#OpenFilter:hover"
            "{"
            "background-color: #bababa;"
            "}"
            "QPushButton#CloseFilter:checked, QPushButton#CloseFilter:hover"
            "{"
            "background-color: #d36c63;"
            "}"
            "QPushButton#New:hover"
            "{"
            "background-color: #9cbc85;"
            "}";
    this->setStyleSheet(style);
}

void BugListTitleWidget::triggerOpenStateButtonToogled(bool toogled)
{
    if (toogled)
        _btnClosedState->setChecked(false);
    else
        _btnClosedState->setChecked(true);
    emit OnFilterStateChanged(BugState::OPEN);
}

void BugListTitleWidget::triggerClosedStateButtonToogled(bool toogled)
{
    if (toogled)
        _btnOpenState->setChecked(false);
    else
        _btnOpenState->setChecked(true);
    emit OnFilterStateChanged(BugState::CLOSED);
}
