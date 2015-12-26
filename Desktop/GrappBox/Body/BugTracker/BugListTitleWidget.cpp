#include "BugListTitleWidget.h"

BugListTitleWidget::BugListTitleWidget(QWidget *parent) : QWidget(parent)
{
    QLabel *lblTitle = new QLabel(tr("Bug list"));
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

