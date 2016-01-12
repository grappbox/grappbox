#include "BugViewCategoryWidget.h"

BugViewCategoryWidget::BugViewCategoryWidget(QWidget *parent) : QWidget(parent)
{
    _viewPage = new QWidget();
    _assignPage = new QWidget();
    _mainWidget = new QStackedWidget(this);
    _mainViewLayout = new QVBoxLayout();
    _mainAssignLayout = new QVBoxLayout();
    _isAPIAssignActivated = true;

    _viewPage->setLayout(_mainViewLayout);
    _assignPage->setLayout(_mainAssignLayout);

    _mainWidget->addWidget(_viewPage);
    _mainWidget->addWidget(_assignPage);
}

void BugViewCategoryWidget::DisableAPIAssignation(const bool disable)
{
    _isAPIAssignActivated = !disable;
}

void BugViewCategoryWidget::DeletePageItems(const BugViewCategoryWidget::BugCategoryPage page)
{
    QVBoxLayout *deletionLayout = (page == BugCategoryPage::VIEW ? _mainViewLayout : _mainAssignLayout);
    QLayoutItem *currentItem;

    while ((currentItem = deletionLayout->itemAt(0)) != NULL)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(NULL);
        deletionLayout->removeItem(currentItem);
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
        qDebug() << "Item found";
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
    QVector<QString> data;
    _creationCategory->setEnabled(false);
    _creationBtn->setEnabled(false);

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    data.append(_creationCategory->text());
    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_CREATETAG, data, this, "TriggerCreateSuccess", "TriggerAPIFailure");
}

void BugViewCategoryWidget::TriggerCheckChange(bool checked, int id, QString name)
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

BugViewCategoryWidget::BugCategoryPage BugViewCategoryWidget::GetCurrentPage()
{
    if (_mainWidget->currentWidget() == _viewPage)
        return BugCategoryPage::VIEW;
    return BugCategoryPage::ASSIGN;
}

const QList<int> BugViewCategoryWidget::GetAllAssignee() const
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

void BugViewCategoryWidget::TriggerCreateSuccess(int UNUSED id, QByteArray data)
{
    QJsonObject json = QJsonDocument::fromJson(data).object();
    BugCheckableLabel *newItem;

    newItem = new BugCheckableLabel(json["tag_id"].toInt(), _creationCategory->text(), false);
    _mainAssignLayout->insertWidget(_mainAssignLayout->count() - 2, newItem);
    emit OnCreated(json["tag_id"].toInt());
    _creationCategory->setText("");
    _creationCategory->setEnabled(true);
    _creationBtn->setEnabled(true);
    this->adjustSize();
    this->update();
}

void BugViewCategoryWidget::TriggerAPIFailure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
    _creationCategory->setEnabled(true);
    _creationBtn->setEnabled(true);
}
