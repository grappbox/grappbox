#include "BugViewAssigneeWidget.h"

BugViewAssigneeWidget::BugViewAssigneeWidget(int bugId, QWidget *parent) : QWidget(parent)
{
    _bugId = bugId;
    _viewPage = new QWidget();
    _assignPage = new QWidget();
    _mainWidget = new QStackedLayout();
    _mainViewLayout = new QVBoxLayout();
    _mainAssignLayout = new QVBoxLayout();
    _mainAssignLayout->setAlignment(Qt::AlignTop);
    _mainViewLayout->setAlignment(Qt::AlignTop);
    _isAPIAssignActivated = true;

    _viewPage->setLayout(_mainViewLayout);
    _assignPage->setLayout(_mainAssignLayout);

    _mainWidget->addWidget(_viewPage);
    _mainWidget->addWidget(_assignPage);
    this->setLayout(_mainWidget);
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
        int id = obj[ITEM_ID].toInt();
        qDebug() << "ID = " << id;
        BugCheckableLabel *widCheckable = new BugCheckableLabel(id, obj["firstname"].toString() + " " + obj["lastname"].toString(), obj[ITEM_ASSIGNED].toBool());

        widCheckable->setMinimumHeight(35);
        widCheckable->setMinimumWidth(230);
        widCheckable->setMaximumWidth(230);
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
        QLabel *newLabel = new QLabel(obj[ITEM_FIRSTNAME].toString() + " " + obj[ITEM_LASTNAME].toString());

        if (!obj[ITEM_ASSIGNED].toBool())
            continue;
        _mainViewLayout->addWidget(newLabel);
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
    int assignId;

    if (_isAPIAssignActivated)
    {
		BEGIN_REQUEST;
		{
			SET_CALL_OBJECT(this);
			ADD_FIELD("bugId", _bugId);
			ADD_FIELD("token", API::SDataManager::GetDataManager()->GetToken());
			ADD_ARRAY("toAdd");
			ADD_ARRAY("toRemove");
			if (checked)
			{
				ADD_FIELD_ARRAY(id, "toAdd");
				SET_ON_DONE("TriggerAssignSuccess");
				SET_ON_FAIL("TriggerAssignFailure");
				assignId = PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNUSER_BUG);
			}
			else
			{
				ADD_FIELD_ARRAY(id, "toRemove");
				SET_ON_DONE("TriggerUnAssignSuccess");
				SET_ON_FAIL("TriggerUnAssignFailure");
				assignId = PUT(API::DP_BUGTRACKER, API::PUTR_ASSIGNUSER_BUG);
			}
		}
		END_REQUEST;
        _apiAssignWaiting[assignId] = id;
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

BugCheckableLabel *BugViewAssigneeWidget::SearchCheckbox(int id)
{
    for (int i = 0; i < _mainAssignLayout->count(); ++i)
    {
        BugCheckableLabel *checkbox = nullptr;
        checkbox = static_cast<BugCheckableLabel *>(_mainAssignLayout->itemAt(i)->widget());

        qDebug() << "ID = "<< (checkbox ? checkbox->GetId() : -1);
        qDebug() << "Passed ID = " << id;
        if (checkbox && checkbox->GetId() == id)
        {
            return checkbox;
        }
    }
    return nullptr;
}

QLabel *BugViewAssigneeWidget::SearchLabel(int id)
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

void BugViewAssigneeWidget::SetBugId(int bugId)
{
    _bugId = bugId;
}

void BugViewAssigneeWidget::TriggerAPIFailure(int  id, QByteArray  data)
{
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}

void BugViewAssigneeWidget::TriggerAssignFailure(int id, QByteArray  data)
{
    BugCheckableLabel *checkbox = SearchCheckbox(_apiAssignWaiting[id]);

    checkbox->SetChecked(false);
    _apiAssignWaiting.remove(id);
    TriggerAPIFailure(id, data);
}

void BugViewAssigneeWidget::TriggerAssignSuccess(int id, QByteArray  data)
{
    BugCheckableLabel *checkbox = nullptr;
    QLabel *newLabel = new QLabel();

    checkbox = SearchCheckbox(_apiAssignWaiting[id]);
    newLabel->setText(checkbox->GetName());

    _mainViewLayout->addWidget(newLabel);
    _apiAssignWaiting.remove(id);
}

void BugViewAssigneeWidget::TriggerUnAssignFailure(int id, QByteArray  data)
{
    BugCheckableLabel *checkbox = SearchCheckbox(_apiAssignWaiting[id]);

    checkbox->SetChecked(true);
    _apiAssignWaiting.remove(id);
    TriggerAPIFailure(id, data);
}

void BugViewAssigneeWidget::TriggerUnAssignSuccess(int id, QByteArray  data)
{
    QLabel *lbl = SearchLabel(_apiAssignWaiting[id]);
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
    _apiAssignWaiting.remove(id);
}
