#include "BugViewCategoryWidget.h"

BugViewCategoryWidget::BugViewCategoryWidget(QWidget *parent) : QWidget(parent)
{
    _viewPage = new QWidget();
    _assignPage = new QWidget();
    _mainWidget = new QStackedWidget(this);
    _mainViewLayout = new QVBoxLayout();
    _mainAssignLayout = new QVBoxLayout();

    _viewPage->setLayout(_mainViewLayout);
    _assignPage->setLayout(_mainAssignLayout);

    _mainWidget->addWidget(_viewPage);
    _mainWidget->addWidget(_assignPage);
}

void BugViewCategoryWidget::DeletePageItems(const BugCategoryPage page)
{
    QVBoxLayout *deletionLayout = (page == BugCategoryPage::VIEW ? _mainViewLayout : _mainAssignLayout);
    QLayoutItem *currentItem;

    while ((currentItem = deletionLayout->itemAt(0)) != NULL)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(NULL);
        deletionLayout->removeItem(currentItem);
        delete currentItem;
    }
    emit OnPageItemsDeleted(page);
}

void BugViewCategoryWidget::CreateAssignPageItems(const QList<QJsonObject> &items)
{
    QList<QJsonObject>::const_iterator  it;
    QHBoxLayout *layCreation = new QHBoxLayout();
    _creationBtn = new QPushButton(tr("Create"));
    _creationCategory = new QLineEdit(tr("Enter the category name here..."));


    for (it = items.begin(); it != items.end(); ++it)
    {
        QJsonObject obj = *it;
        BugCheckableLabel *widCheckable = new BugCheckableLabel(obj[ITEM_ID].toInt(), obj[ITEM_NAME].toString(), obj[ITEM_ASSIGNED].toBool());

        QObject::connect(widCheckable, SIGNAL(OnCheckChanged(bool,int,QString)), this, SLOT(TriggerCheckChange(bool,int, QString)));
        _mainAssignLayout->addWidget(widCheckable);
    }
    QObject::connect(_creationBtn, SIGNAL(released()), this, SLOT(TriggerCreateReleased()));
    layCreation->addWidget(_creationCategory);
    layCreation->addWidget(_creationBtn);
    _mainAssignLayout->addLayout(layCreation);
    emit OnPageItemsCreated(BugCategoryPage::ASSIGN);
}

void BugViewCategoryWidget::CreateViewPageItems(const QList<QJsonObject> &items)
{
    QList<QJsonObject>::const_iterator  it;

    for (it = items.begin(); it != items.end(); ++it)
    {
        QJsonObject obj = *it;

        if (!obj[ITEM_ASSIGNED].toBool())
            continue;
        _mainViewLayout->addWidget(new QLabel(obj[ITEM_NAME].toString()));
    }
    emit OnPageItemsCreated(BugCategoryPage::VIEW);
}

void BugViewCategoryWidget::TriggerOpenPage(const BugCategoryPage page)
{
    _mainWidget->setCurrentWidget(page == BugCategoryPage::ASSIGN ? _assignPage : _viewPage);
    emit OnPageChanged(page);
}

void BugViewCategoryWidget::TriggerCreateReleased()
{
    _creationCategory->setEnabled(false);
    _creationBtn->setEnabled(false);
    //TODO : Link API
    emit OnCreated(-1); //After creation link API
    emit OnAssigned(-1, _creationCategory->text()); //After assignation link API
    _creationCategory->setEnabled(true);
    _creationBtn->setEnabled(true);
}

void BugViewCategoryWidget::TriggerCheckChange(bool checked, int id, QString name)
{
    //TODO : Link API
    if (checked)
        emit OnAssigned(id, name);
    else
        emit OnDelAssigned(id, name);
}
