#include "BugViewAssigneeWidget.h"

BugViewAssigneeWidget::BugViewAssigneeWidget(QWidget *parent) : QWidget(parent)
{
    _viewPage = new QWidget();
    _assignPage = new QWidget();
    _mainWidget = new QStackedWidget(this);
    _mainViewLayout = new QVBoxLayout();
    _mainAssignLayout = new QVBoxLayout();
    _isAPIAssignActivated = true;

    _viewPage->setLayout(_mainViewLayout);
    _assignPage->setLayout(_mainAssignLayout);

    this->setMinimumHeight(450);

    _mainWidget->addWidget(_viewPage);
    _mainWidget->addWidget(_assignPage);
}

void BugViewAssigneeWidget::DisableAPIAssignation(const bool disable)
{
    _isAPIAssignActivated = !disable;
}

void BugViewAssigneeWidget::DeletePageItems(const BugViewAssigneeWidget::BugAssigneePage page)
{
    QVBoxLayout *deletionLayout = (page == BugAssigneePage::VIEW ? _mainViewLayout : _mainAssignLayout);
    QLayoutItem *currentItem;

    while ((currentItem = deletionLayout->itemAt(0)) != nullptr)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(nullptr);
        deletionLayout->removeItem(currentItem);
    }
    emit OnPageItemsDeleted(page);
}

void BugViewAssigneeWidget::CreateAssignPageItems(const QList<QJsonObject> &items)
{
    QList<QJsonObject>::const_iterator  it;

    for (it = items.begin(); it != items.end(); ++it)
    {
        QJsonObject obj = *it;
        BugCheckableLabel *widCheckable = new BugCheckableLabel(obj[ITEM_ID].toInt(), obj[ITEM_FIRSTNAME].toString() + " " + obj[ITEM_LASTNAME].toString(), obj[ITEM_ASSIGNED].toBool());

        QObject::connect(widCheckable, SIGNAL(OnCheckChanged(bool,int,QString)), this, SLOT(TriggerCheckChange(bool,int, QString)));
        _mainAssignLayout->addWidget(widCheckable);
    }
    emit OnPageItemsCreated(BugAssigneePage::ASSIGN);
}

void BugViewAssigneeWidget::CreateViewPageItems(const QList<QJsonObject> &items)
{
    QList<QJsonObject>::const_iterator  it;

    for (it = items.begin(); it != items.end(); ++it)
    {
        QJsonObject obj = *it;

        if (!obj[ITEM_ASSIGNED].toBool())
            continue;
        _mainViewLayout->addWidget(new QLabel(obj[ITEM_FIRSTNAME].toString() + " " + obj[ITEM_LASTNAME].toString()));
    }
    emit OnPageItemsCreated(BugAssigneePage::VIEW);
}

void BugViewAssigneeWidget::TriggerOpenPage(const BugAssigneePage page)
{
    _mainWidget->setCurrentWidget(page == BugAssigneePage::ASSIGN ? _assignPage : _viewPage);
    emit OnPageChanged(page);
}

void BugViewAssigneeWidget::TriggerCheckChange(bool checked, int id, QString name)
{
    if (_isAPIAssignActivated)
    {
        //TODO : Link API
    }
    if (checked)
        emit OnAssigned(id, name);
    else
        emit OnDelAssigned(id, name);
}

BugViewAssigneeWidget::BugAssigneePage BugViewAssigneeWidget::GetCurrentPage() const
{
    if (_mainWidget->currentWidget() == _viewPage)
        return BugAssigneePage::VIEW;
    return BugAssigneePage::ASSIGN;
}

const QList<int> BugViewAssigneeWidget::GetAllAssignee() const
{
    QLayoutItem *item;
    QList<int> idAssigned;

    while ((item = _mainAssignLayout->takeAt(0)) != 0)
    {
        BugCheckableLabel *checkableLabel;

        if (!item->widget())
            continue;
        checkableLabel = static_cast<BugCheckableLabel *>(item->widget());
        if (checkableLabel->IsChecked())
            idAssigned.append(checkableLabel->GetId());
    }
    return idAssigned;
}
