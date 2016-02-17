#include <QMessageBox>
#include <QDebug>
#include <QXmlStreamReader>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonArray>
#include "SDataManager.h"
#include "CalendarEventForm.h"
#include "utils.h"
#include "ListAddRemoveSelection.h"

CalendarEventForm::CalendarEventForm(Event *event, QMap<int, QString> &project, QWidget *callBackEvent)
{
	_CurrentEvent = event;
	_CallBackWidget = callBackEvent;

	setFixedSize(400, 800);

	_MainLayout = new QFormLayout();
	_DateStartLayout = new QHBoxLayout();
	_DateEndLayout = new QHBoxLayout();
	_Buttons = new QHBoxLayout();

	_TitleEdit = new QLineEdit();
	_DescriptionEdit = new QTextEdit();
	_DateStart = new QDateEdit();
	_DateStart->setCalendarPopup(true);
	_TimeStart = new QTimeEdit();
	_DateEnd = new QDateEdit();
	_DateEnd->setCalendarPopup(true);
	_TimeEnd = new QTimeEdit();

	_SelectionProject = new QComboBox();
	for (QMap<int, QString>::iterator it = project.begin(); it != project.end(); ++it)
	{
		_SelectionProject->addItem(it.value(), QVariant(it.key()));
	}
	_SelectionProject->setCurrentIndex(0);
	_SelectionType = new QComboBox();
	_UseTypeIcon = new QCheckBox("Use the icon of type");

	_UploadWidget = new ImageUploadWidget();

	_DateStartLayout->addWidget(_DateStart, 4);
	_DateStartLayout->addWidget(_TimeStart, 4);
	_DateStartLayout->addWidget(new QLabel(" to "), 1);
	_DateStartLayout->addWidget(_DateEnd, 4);
	_DateStartLayout->addWidget(_TimeEnd, 4);

	_AreaAssociated = new QScrollArea();
	_AreaAssociated->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
	QWidget *userAssociatedWidget = new QWidget();
	_UserAssociated = new QVBoxLayout();
	_UserAssociated->setSpacing(0);
	_UserAssociated->setContentsMargins(20, 0, 0, 0);
	userAssociatedWidget->setLayout(_UserAssociated);
	_AreaAssociated->setWidget(userAssociatedWidget);
	_AreaAssociated->setWidgetResizable(true);

	_AreaNotAssociated = new QScrollArea();
	_AreaNotAssociated->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
	QWidget *userNotAssociatedWidget = new QWidget();
	_UserNotAssociated = new QVBoxLayout();
	_UserNotAssociated->setSpacing(0);
	_UserNotAssociated->setContentsMargins(20, 0, 0, 0);
	userNotAssociatedWidget->setLayout(_UserNotAssociated);
	_AreaNotAssociated->setWidget(userNotAssociatedWidget);
	_AreaNotAssociated->setWidgetResizable(true);

	_Save = new QPushButton("Save");
	_Remove = new QPushButton("Delete event");

	_Buttons->addWidget(_Save);
	_Buttons->addWidget(_Remove);

	_MainLayout->addRow(_TitleEdit);
	_MainLayout->addRow(_DateStartLayout);
	_MainLayout->addRow(new QLabel("Event Detail"));
	_MainLayout->addRow("Type", _SelectionType);
	_MainLayout->addRow("Description", _DescriptionEdit);
	_MainLayout->addRow(_UseTypeIcon);
	_MainLayout->addRow(_UploadWidget);
	_MainLayout->addRow("Project", _SelectionProject);
	_MainLayout->addRow(new QLabel("Event's participants"));
	_MainLayout->addRow(_AreaAssociated);
	_MainLayout->addRow(new QLabel("Disponible participants"));
	_MainLayout->addRow(_AreaNotAssociated);
	_MainLayout->addRow(_Buttons);

	setLayout(_MainLayout);

	QObject::connect(_SelectionProject, SIGNAL(currentIndexChanged(int)), this, SLOT(OnProjectSelected()));
	QObject::connect(_Save, SIGNAL(clicked(bool)), this, SLOT(OnSave()));
	QObject::connect(_Remove, SIGNAL(clicked(bool)), this, SLOT(OnRemove()));
	QObject::connect(_UserAssociated, SIGNAL(itemSelectionChanged()), this, SLOT(OnListUserSelected()));

	_EventLoaded = true;

	QVector<QString> data;
	for (QMap<int, QString>::iterator it = project.begin(); it != project.end(); ++it)
	{
		data.push_back(USER_TOKEN);
		data.push_back(TO_STRING(it.key()));
		int id = DATA_CONNECTOR->Get(API::DP_PROJECT, API::GR_PROJECT_USERS_ALL, data, this, "OnLoadProjectUserDone", "OnLoadProjectUserFail");
		data.clear();
		_PendingCallProject[id] = it.key();
	}
	data.push_back(USER_TOKEN);
	DATA_CONNECTOR->Get(API::DP_CALENDAR, API::GR_TYPE_EVENT, data, this, "OnEventTypeLoadDone", "OnEventTypeLoadFail");
	data.clear();

	setDisabled(true);

	if (event)
	{
		_EventLoaded = false;
		_TitleEdit->setText(event->Title);
		_DateStart->setDate(event->Start.date());
		_TimeStart->setTime(event->Start.time());
		_DateEnd->setDate(event->End.date());
		_TimeEnd->setTime(event->End.time());
		_DescriptionEdit->setText(event->Description);
		_UploadWidget->setImage(event->Icon);
		data.push_back(USER_TOKEN);
		data.push_back(TO_STRING(event->EventId));
		DATA_CONNECTOR->Get(API::DP_CALENDAR, API::GR_EVENT, data, this, "OnLoadEventDone", "OnLoadEventFail");
		int currentIndex = _SelectionProject->findData(QVariant(event->ProjectId));
		if (currentIndex != -1)
			_SelectionProject->setCurrentIndex(currentIndex);
	}
}

void CalendarEventForm::OnSave()
{
	if (_CurrentEvent == nullptr)
	{
		BEGIN_REQUEST;
		{
			SET_CALL_OBJECT(this);
			SET_ON_DONE("OnSaveEventDone");
			SET_ON_FAIL("OnSaveEventFail");
			ADD_FIELD("token", USER_TOKEN);
			ADD_FIELD("title", _TitleEdit->text());
			ADD_FIELD("description", _DescriptionEdit->toPlainText());
			if (_UseTypeIcon->isChecked())
				ADD_FIELD("icon", "");
			else
				ADD_FIELD("icon", _UploadWidget->getEncodedImage());
			ADD_FIELD("typeId", _SelectionType->currentData());
			ADD_FIELD("begin", _DateStart->date().toString("yyyy-MM-dd") + " " + _TimeStart->time().toString("HH:mm:ss"));
			ADD_FIELD("end", _DateEnd->date().toString("yyyy-MM-dd") + " " + _TimeEnd->time().toString("HH:mm:ss"));
			GENERATE_JSON_DEBUG;
			POST(API::DP_CALENDAR, API::PR_POST_EVENT);
		}
		END_REQUEST;
	}
	else
	{
		BEGIN_REQUEST;
		{
			SET_CALL_OBJECT(this);
			SET_ON_DONE("OnSaveEventDone");
			SET_ON_FAIL("OnSaveEventFail");
			ADD_FIELD("token", USER_TOKEN);
			ADD_FIELD("title", _TitleEdit->text());
			ADD_FIELD("description", _DescriptionEdit->toPlainText());
			if (_UseTypeIcon->isChecked())
				ADD_FIELD("icon", "");
			else
				ADD_FIELD("icon", _UploadWidget->getEncodedImage());
			ADD_FIELD("eventId", _CurrentEvent->EventId);
			ADD_FIELD("typeId", _CurrentEvent->EventTypeId);
			ADD_FIELD("begin", _DateStart->date().toString("yyyy-MM-dd") + " " + _TimeStart->time().toString("HH:mm:ss"));
			ADD_FIELD("end", _DateEnd->date().toString("yyyy-MM-dd") + " " + _TimeEnd->time().toString("HH:mm:ss"));
			PUT(API::DP_CALENDAR, API::PUTR_EDIT_EVENT);
		}
		END_REQUEST;
	}
	/*QVector<QString> data;
	data.push_back(USER_TOKEN);
	if (_CurrentEvent != nullptr)
		data.push_back(TO_STRING(_CurrentEvent->EventId));
	data.push_back(_TitleEdit->text());
	data.push_back(_DescriptionEdit->toPlainText());
	if (_UseTypeIcon->isChecked())
		data.push_back("");
	else
		data.push_back(_UploadWidget->getEncodedImage());
	data.push_back(TO_STRING(_CurrentEvent->EventTypeId));
	data.push_back(_DateStart->date().toString("yyyy-MM-dd") + " " + _TimeStart->time().toString("HH:mm:ss"));
	data.push_back(_DateEnd->date().toString("yyyy-MM-dd") + " " + _TimeEnd->time().toString("HH:mm:ss"));

		DATA_CONNECTOR->Post(API::DP_CALENDAR, API::PR_POST_EVENT, data, this, "OnSaveEventDone", "OnSaveEventFail");
	else
		DATA_CONNECTOR->Put(API::DP_CALENDAR, API::PUTR_EDIT_EVENT, data, this, "OnSaveEventDone", "OnSaveEventFail");
*/}

void CalendarEventForm::OnRemove()
{
	if (QMessageBox::warning(this, "Delete event", "Area you sure you want to delete this event ?", QMessageBox::Yes, QMessageBox::No) == QMessageBox::Yes)
	{
		emit Remove(_CurrentEvent);
		close();
	}
}

void CalendarEventForm::OnEventTypeLoadDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);

	QJsonArray array = doc.object()["data"].toObject()["array"].toArray();
	for (QJsonValueRef ref : array)
	{
		QJsonObject obj = ref.toObject();
		_Type[obj["id"].toInt()] = obj["name"].toString();
	}
	_TypeLoaded = true;
	EndLoad();
}

void CalendarEventForm::OnEventTypeLoadFail(int id, QByteArray data)
{

}

void CalendarEventForm::OnLoadProjectUserDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);

	int projectId = _PendingCallProject[id];

	qDebug() << doc.toJson(QJsonDocument::Indented);

	QJsonObject obj = doc.object()["data"].toObject();
	QJsonArray arr = obj["array"].toArray();
	for (QJsonValueRef item : arr)
	{
		QJsonObject curObj = item.toObject();
		QString name = curObj["firstname"].toString() + " " + curObj["lastname"].toString();
		_UserProjectsList[projectId].push_back(QPair<int, QString>(curObj["id"].toInt(), name));
	}
	_PendingCallProject.remove(id);
	EndLoad();
}

void CalendarEventForm::OnLoadProjectUserFail(int id, QByteArray data)
{
}

void CalendarEventForm::OnLoadEventDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);

	QJsonObject obj = doc.object()["data"].toObject();
	QJsonArray arr = obj["users"].toArray();
	for (QJsonValueRef item : arr)
	{
		QJsonObject curObj = item.toObject();
		_AssociatedUserForProject[_CurrentEvent->ProjectId].push_back(curObj["id"].toInt());
		_IdsAtStart.push_back(curObj["id"].toInt());
	}
	_EventLoaded = true;
	EndLoad();
}

void CalendarEventForm::OnLoadEventFail(int id, QByteArray data)
{
}

void CalendarEventForm::OnSaveAssociatedDone(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnSaveAssociatedFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnSaveEventDone(int id, QByteArray data)
{
	int idUser;
	QJsonDocument doc = QJsonDocument::fromJson(data);
	QJsonObject obj = doc.object()["data"].toObject();
	idUser = obj["id"].toInt();
	SHOW_JSON(data);
	QList<int> newToAdd;
	QList<int> oldToRemove;
	int selectedProject = _SelectionProject->currentData().toInt();
	for (int id : _AssociatedUserForProject[selectedProject])
	{
		if (!_IdsAtStart.contains(id))
			newToAdd.push_back(id);
	}
	for (int id : _IdsAtStart)
	{
		if (!_AssociatedUserForProject.contains(id))
			oldToRemove.push_back(id);
	}
	QVector<QString> newData;
	newData.push_back(USER_TOKEN);
	newData.push_back(TO_STRING(idUser));
	for (int id : newToAdd)
	{
		newData.push_back(TO_STRING(id));
	}
	newData.push_back("#");
	for (int id : oldToRemove)
	{
		newData.push_back(TO_STRING(id));
	}
	DATA_CONNECTOR->Put(API::DP_CALENDAR, API::PUTR_SET_PARTICIPANT, newData, this, "OnSaveAssociatedDone", "OnSaveAssociatedFail");
}

void CalendarEventForm::OnSaveEventFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnRemoveEventDone(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnRemoveEventFail(int id, QByteArray data)
{
	setDisabled(false);
	close();
}

void CalendarEventForm::OnListUserSelected(int id)
{
	int selectedProject = _SelectionProject->currentData().toInt();
	if (_AssociatedUserForProject[selectedProject].contains(id))
		_AssociatedUserForProject[selectedProject].removeAll(id);
	else
		_AssociatedUserForProject[selectedProject].push_back(id);
	EndLoad(false);
}

void CalendarEventForm::OnProjectSelected()
{
	EndLoad(false);
}

void CalendarEventForm::EndLoad(bool checkAPILoad)
{
	if (checkAPILoad && (_PendingCallProject.size() > 0 || !_EventLoaded || !_TypeLoaded))
	{
		return;
	}
	int selectedProject = _SelectionProject->currentData().toInt();
	while (QLayoutItem *item = _UserAssociated->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	while (QLayoutItem *item = _UserNotAssociated->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	_SelectionType->clear();
	for (QMap<int, QString>::iterator it = _Type.begin(); it != _Type.end(); ++it)
	{
		_SelectionType->addItem(it.value(), QVariant(it.key()));
	}
	QList<QPair<int, QString> > added;
	QList<QPair<int, QString> > notAdded;
	QPair<int, QString> owner;
	for (QPair<int, QString> item : _UserProjectsList[selectedProject])
	{
		if (item.first == USER_ID)
		{
			owner = item;
			continue;
		}
		if (_AssociatedUserForProject[selectedProject].contains(item.first))
			added.push_back(item);
		else
			notAdded.push_back(item);
	}
	_UserAssociated->addWidget(new QLabel(owner.second));
	for (QPair<int, QString> item : added)
	{
		ListAddRemoveSelection *itemW = new ListAddRemoveSelection(item.first, false, item.second);
		_UserAssociated->addWidget(itemW);
		QObject::connect(itemW, SIGNAL(Selected(int)), this, SLOT(OnListUserSelected(int)));

	}
	for (QPair<int, QString> item : notAdded)
	{
		ListAddRemoveSelection *itemW = new ListAddRemoveSelection(item.first, true, item.second);
		_UserNotAssociated->addWidget(itemW);
		QObject::connect(itemW, SIGNAL(Selected(int)), this, SLOT(OnListUserSelected(int)));
	}
	setDisabled(false);
}
