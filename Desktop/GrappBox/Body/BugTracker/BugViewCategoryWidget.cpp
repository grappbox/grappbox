#include "BugViewCategoryWidget.h"

BugViewCategoryWidget::BugViewCategoryWidget(int bugId, QWidget *parent) : QWidget(parent)
{
    _bugId = bugId;
    _viewPage = new QWidget();
    _assignPage = new QWidget();
    _mainWidget = new QStackedLayout();
    _mainViewLayout = new QVBoxLayout();
    _mainAssignLayout = new QVBoxLayout();
    _isAPIAssignActivated = true;
    _mainViewLayout->setAlignment(Qt::AlignTop);
    _mainAssignLayout->setAlignment(Qt::AlignTop);

    _viewPage->setLayout(_mainViewLayout);
    _assignPage->setLayout(_mainAssignLayout);

    _mainWidget->addWidget(_viewPage);
    _mainWidget->addWidget(_assignPage);
    this->setLayout(_mainWidget);
}

void BugViewCategoryWidget::DisableAPIAssignation(const bool disable)
{
    _isAPIAssignActivated = !disable;
}

void BugViewCategoryWidget::DeletePageItems(const BugViewCategoryWidget::BugCategoryPage page)
{
    QVBoxLayout *deletionLayout = (page == BugCategoryPage::VIEW ? _mainViewLayout : _mainAssignLayout);
    QLayoutItem *currentItem;

    while ((currentItem = deletionLayout->itemAt(0)) != nullptr)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(nullptr);
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
    QVector<QString> data;
    int assignId;

    if (_isAPIAssignActivated)
    {
        data.append(API::SDataManager::GetDataManager()->GetToken());
        data.append(QString::number(_bugId));
        data.append(QString::number(id));
        if (checked)
            assignId = API::SDataManager::GetCurrentDataConnector()->Put(API::DP_BUGTRACKER, API::PUTR_ASSIGNTAG, data, this, "TriggerAssignSuccess", "TriggerAssignFailure");
        else
            assignId = API::SDataManager::GetCurrentDataConnector()->Delete(API::DP_BUGTRACKER, API::DR_REMOVE_BUGTAG, data, this, "TriggerUnAssignSuccess", "TriggerUnAssignFailure");
        _apiAssignationWait[assignId] = id;
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

void BugViewCategoryWidget::SetBugId(const int bugId)
{
    _bugId = bugId;
}

BugCheckableLabel *BugViewCategoryWidget::SearchCheckbox(int id)
{
    for (int i = 0; i < _mainAssignLayout->count(); ++i)
    {
        BugCheckableLabel *checkbox = nullptr;

        if (_mainAssignLayout->itemAt(i)->widget())
            checkbox = static_cast<BugCheckableLabel *>(_mainAssignLayout->itemAt(i)->widget());
        if (checkbox && checkbox->GetId() == id)
            return checkbox;
    }
    return nullptr;
}

QLabel *BugViewCategoryWidget::SearchLabel(int id)
{
    BugCheckableLabel *checkbox = SearchCheckbox(id);

    if (!checkbox)
        return nullptr;
    for (int i = 0; i < _mainViewLayout->count(); ++i)
    {
        QLabel *lbl = static_cast<QLabel *>(_mainViewLayout->itemAt(i)->widget());

        if (lbl && lbl->text() == checkbox->GetName())
            return lbl;
    }
    return nullptr;
}

void BugViewCategoryWidget::TriggerAssignSuccess(int id, QByteArray UNUSED data)
{
    BugCheckableLabel *checkbox = SearchCheckbox(_apiAssignationWait[id]);
    QLabel *newLabel = new QLabel(checkbox->GetName());

    _mainViewLayout->addWidget(newLabel);
    _apiAssignationWait.remove(id);
}

void BugViewCategoryWidget::TriggerAssignFailure(int id, QByteArray UNUSED data)
{
    BugCheckableLabel *checkbox = SearchCheckbox(_apiAssignationWait[id]);

    checkbox->SetChecked(false);
    _apiAssignationWait.remove(id);
    TriggerAPIFailure(id, data);
}

void BugViewCategoryWidget::TriggerUnAssignSuccess(int id, QByteArray UNUSED data)
{
    QLabel *lbl = SearchLabel(_apiAssignationWait[id]);
    QLayoutItem *item;

    for (int i = 0; i < _mainViewLayout->count(); ++i)
    {
        item = _mainViewLayout->itemAt(i);

        if (item->widget() && item->widget() == lbl)
        {
            item->widget()->setParent(nullptr);
            _mainViewLayout->removeItem(item);
        }
    }
    _apiAssignationWait.remove(id);
}

void BugViewCategoryWidget::TriggerUnAssignFailure(int id, QByteArray UNUSED data)
{
    BugCheckableLabel *checkbox = SearchCheckbox(_apiAssignationWait[id]);

    checkbox->SetChecked(true);
    _apiAssignationWait.remove(id);
    TriggerAPIFailure(id, data);
}
