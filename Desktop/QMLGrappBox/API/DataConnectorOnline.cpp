#include <QtWidgets/QMessageBox>
#include <QDebug>
#include <QException>
#include <QByteArray>
#include <QtNetwork/QNetworkRequest>
#include "DataConnectorOnline.h"
#include "SDebugLog.h"

//#define LARGE_DEBUG
#define MIN_DEBUG

#ifdef LARGE_DEBUG
#include <QJsonDocument>
#include <QJsonObject>
#endif

using namespace API;

DataConnectorOnline::DataConnectorOnline()
{
	_Manager = new QNetworkAccessManager();

	// Initialize Get request
    _GetMap[GR_CALENDAR] = "planning/getmonth";
	_GetMap[GR_CALENDAR_DAY] = "";
	_GetMap[GR_LIST_GANTT] = "";
	_GetMap[GR_LIST_PROJECT] = "dashboard/getprojectsglobalprogress";
    _GetMap[GR_TEAM_OCCUPATION] = "dashboard/getteamoccupation";
    _GetMap[GR_NEXT_MEETING] = "dashboard/getnextmeetings";
    _GetMap[GR_PROJECT] = "projects/getinformations";
	_GetMap[GR_CREATOR_PROJECT] = "";
	_GetMap[GR_LIST_MEMBER_PROJECT] = "dashboard/getteamoccupation";
	_GetMap[GR_LIST_MEETING] = "dashboard/getnextmeetings";
    _GetMap[GR_LIST_TASK] = "tasks/getprojecttasks";
    _GetMap[GR_LIST_TIMELINE] = "timeline/gettimelines";
    _GetMap[GR_LIST_TASK_TAG] = "tasks/getprojecttags";
	_GetMap[GR_TASK] = "";
    _GetMap[GR_TIMELINE] = "timeline/getmessages";
	_GetMap[GR_COMMENT_TIMELINE] = "timeline/getcomments";
    _GetMap[GR_USER_DATA] = "user/getuserbasicinformations";
	_GetMap[GR_WHITEBOARD] = "";
    _GetMap[GR_LOGOUT] = "accountadministration/logout";
	_GetMap[GR_USER_SETTINGS] = "";
    _GetMap[GR_PROJECTS_USER] = "projects/getusertoproject";
    _GetMap[GR_PROJECT_ROLE] = "roles/getprojectroles";
    _GetMap[GR_PROJECT_USERS] = "projects/getusertoproject";
	_GetMap[GR_PROJECT_CANCEL_DELETE] = "";
    _GetMap[GR_PROJECT_USER_ROLE] = "roles/getrolebyprojectanduser";
    _GetMap[GR_CUSTOMER_ACCESSES] = "projects/getcustomeraccessbyproject";
	_GetMap[GR_CUSTOMER_ACCESS_BY_ID] = "";
	_GetMap[GR_XLAST_BUG_OFFSET] = "bugtracker/getlasttickets";
	_GetMap[GR_XLAST_BUG_OFFSET_BY_STATE] = "bugtracker/getticketsbystate";
	_GetMap[GR_XLAST_BUG_OFFSET_CLOSED] = "bugtracker/getlastclosedtickets";
	_GetMap[GR_PROJECTBUG_ALL] = "bugtracker/gettickets";
	_GetMap[GR_BUGCOMMENT] = "bugtracker/getcomments";
	_GetMap[GR_GETBUGS_STATUS] = "bugtracker/getstates";
	_GetMap[GR_PROJECTBUGTAG_ALL] = "bugtracker/getprojecttags";
	_GetMap[GR_PROJECT_USERS_ALL] = "projects/getusertoproject";
    _GetMap[GR_BUG_OPEN] = "bugtracker/gettickets";
    _GetMap[GR_BUG_CLOSED] = "bugtracker/getclosedtickets";
    _GetMap[GR_BUG_YOURS] = "bugtracker/getticketsbyuser";
    _GetMap[GR_EVENT] = "event/getevent";
    _GetMap[GR_TYPE_EVENT] = "event/gettypes";
    _GetMap[GR_LIST_CLOUD] = "cloud/list";
    _GetMap[GR_DOWNLOAD_FILE] = "cloud/file";
    _GetMap[GR_DOWNLOAD_SECURE_FILE] = "cloud/filesecured";

	// Initialize Post request
	_PostMap[PR_LOGIN] = "accountadministration/login";
    _PostMap[PR_ROLE_ADD] = "roles/addprojectroles";
    _PostMap[PR_ROLE_ASSIGN] = "roles/assignpersontorole";
    _PostMap[PR_CUSTOMER_GENERATE_ACCESS] = "projects/generatecustomeraccess";
	_PostMap[PR_CREATE_BUG] = "bugtracker/postticket";
	_PostMap[PR_COMMENT_BUG] = "bugtracker/postcomment";
    _PostMap[PR_CREATE_BUG_TAG] = "bugtracker/tagcreation";
    _PostMap[PR_MESSAGE_TIMELINE] = "timeline/postmessage";
    _PostMap[PR_POST_EVENT] = "event/postevent";
	_PostMap[PR_NEW_WHITEBOARD] = "";
    _PostMap[PR_CREATE_DIRECTORY] = "cloud/createdir";
    _PostMap[PR_OPEN_STREAM] = "cloud/stream";
    _PostMap[PR_ADD_TAG_TASK] = "tasks/tagcreation";
    _PostMap[PR_ADD_USER_PROJECT] = "projects/addusertoproject";
    _PostMap[PR_CREATE_PROJECT] = "projects/projectcreation";

	// Initialize Delete request
    _DeleteMap[DR_PROJECT_ROLE] = "roles/delprojectroles";
	_DeleteMap[DR_ROLE_DETACH] = "";
    _DeleteMap[DR_PROJECT_USER] = "projects/removeusertoproject";
	_DeleteMap[DR_PROJECT] = "";
    _DeleteMap[DR_CUSTOMER_ACCESS] = "projects/delcustomeraccess";
	_DeleteMap[DR_CLOSE_TICKET_OR_COMMENT] = "bugtracker/closeticket";
	_DeleteMap[DR_REMOVE_BUGTAG] = "bugtracker/deletetag";
    _DeleteMap[DR_REMOVE_TAG_TO_BUG] = "bugtracker/removetag";
	_DeleteMap[DR_REMOVE_EVENT] = "";
    _DeleteMap[DR_ARCHIVE_MESSAGE_TIMELINE] = "timeline/archivemessage";
    _DeleteMap[DR_CLOSE_STREAM] = "cloud/stream";
    _DeleteMap[DR_DELETE_ITEM] = "cloud/file";
    _DeleteMap[DR_DELETE_SECURE_ITEM] = "cloud/filesecured";
    _DeleteMap[DR_TASK_TAG] = "tasks/deletetag";

	// Initialize Put request
    _PutMap[PUTR_USERSETTINGS] = "user/basicinformations";
	_PutMap[PUTR_PROJECTSETTINGS] = "";
	_PutMap[PUTR_INVITE_USER] = "";
	_PutMap[PUTR_ASSIGNTAG] = "bugtracker/assigntag";
	_PutMap[PUTR_EDIT_EVENT] = "event/editevent";
	_PutMap[PUTR_SET_PARTICIPANT] = "bugtracker/setparticipants";
	_PutMap[PUTR_EDIT_COMMENTBUG] = "bugtracker/editcomment";
	_PutMap[PUTR_EDIT_BUG] = "bugtracker/editticket";
    _PutMap[PUTR_EDIT_MESSAGE_TIMELINE] = "timeline/editmessage";
    _PutMap[PUTR_SEND_CHUNK] = "cloud/file";
    _PutMap[PUTR_ASSIGNUSER_BUG] = "bugtracker/setparticipants";
    _PutMap[PUTR_REOPEN_BUG] = "bugtracker/reopenticket";
    _PutMap[PUTR_EDIT_PROJECT] = "projects/updateinformations";
    _PutMap[PUTR_ROLE_USER] = "roles/putpersonrole";
    _PutMap[PUTR_ROLE] = "roles/putprojectroles";
}

void DataConnectorOnline::unregisterObjectRequest(QObject *obj)
{
    for (QMap<QNetworkReply*, DataConnectorCallback>::iterator it = _CallBack.begin(); it != _CallBack.end(); ++it)
    {
        if (it.value()._Request == obj)
            it.value()._Request = nullptr;
    }
}

void DataConnectorOnline::OnResponseAPI()
{
	QNetworkReply *request = dynamic_cast<QNetworkReply*>(QObject::sender());

    if (request == nullptr || !_Request.contains(request))
	{
		return;
    }
    QByteArray req = request->readAll();

#ifdef MIN_DEBUG
    qDebug() << "[API][RESPONSE]["  << request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString().toStdString().c_str() << "] " << request->request().url().toString().toStdString().c_str();
#endif

#ifdef LARGE_DEBUG
    qDebug() << "[DataConnectorOnline] Receive status code : " << request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString();
    qDebug() << "[DataConnectorOnline] Receive response from API with the url : " + request->request().url().toString();
    qDebug() << req;
#endif
    //FINISH_REQUEST(_Request[request], request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString(), request->errorString(), req);
	if (request->error())
	{
#ifdef LARGE_DEBUG
        qDebug() << "[DataConnectorOnline] Error with response : " << request->errorString().toStdString().c_str();
		if (request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString()[0] == '4')
		{
			QJsonDocument doc = QJsonDocument::fromJson(req);
			QJsonObject info = doc.object()["info"].toObject();
            qDebug() << "[DataConnectorOnline] Error code : " << info["return_code"].toString().toStdString().c_str();
            qDebug() << "[DataConnectorOnline] Error message : " << info["return_message"].toString().toStdString().c_str();
            LOG(QString(req));
        }
#endif
#ifdef MIN_DEBUG
        qDebug() << "[API][RESPONSE][ERROR] " << request->errorString().toStdString().c_str();
#endif
        if (_CallBack[request]._Request != nullptr)
            QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotFailure, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
	}
	else
    {
        if (request->attribute(QNetworkRequest::HttpStatusCodeAttribute).toString() == "302")
        {
            req = QByteArray::fromStdString(request->header(QNetworkRequest::LocationHeader).toString().toStdString());
        }
        if (_CallBack[request]._Request != nullptr)
            QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotSuccess, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
    }
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case PR_LOGIN:
		reply = Login(data);
		break;
	case PR_ROLE_ADD:
		reply = AddRole(data);
		break;
	case PR_ROLE_ASSIGN:
		reply = AttachRole(data);
		break;
	case PR_CUSTOMER_GENERATE_ACCESS:
		reply = CustomerGenerateAccess(data);
		break;
    case PR_MESSAGE_TIMELINE:
		reply = PostMessageTimeline(data);
		break;
	case PR_CREATE_BUG:
		reply = OpenBug(data);
		break;
	case PR_COMMENT_BUG:
		reply = CommentBug(data);
		break;
	case PR_DELETEUSER_BUG:
		reply = DeleteUserToTicket(data);
		break;
	case PR_POST_EVENT:
		reply = PostEvent(data);
		break;
	case PR_NEW_WHITEBOARD:
		reply = PostNewWhiteboard(data);
		break;
	}
	if (reply == nullptr)
        throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
    _Request[reply] = maxInt;
	return maxInt;
}

int DataConnectorOnline::Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case GR_LOGOUT:
		reply = Logout(data);
		break;

	case GR_LIST_PROJECT:
		reply = GetActionDeprecated("dashboard/getprojectsglobalprogress", data);
		break;

	case GR_PROJECT:
		reply = GetActionDeprecated("projects/getinformations", data);
		break;

	case GR_CREATOR_PROJECT:
		reply = GetActionDeprecatedOld("dashboard/getprojectcreator", data);
		break;

	case GR_LIST_MEMBER_PROJECT:
		reply = GetActionDeprecatedOld("dashboard/getteamoccupation", data);
		break;

	case GR_LIST_MEETING:
		reply = GetActionDeprecatedOld("dashboard/getnextmeetings", data);
		break;
	case GR_USER_SETTINGS:
		reply = GetActionDeprecatedOld("user/basicinformations", data);
		break;
	case GR_PROJECTS_USER:
		reply = GetActionDeprecatedOld("user/getprojects", data);
		break;
	case GR_PROJECT_ROLE:
		reply = GetActionDeprecatedOld("roles/getprojectroles", data);
		break;
	case GR_PROJECT_USERS:
		reply = GetActionDeprecatedOld("dashboard/getprojectpersons", data);
		break;
	case GR_PROJECT_CANCEL_DELETE:
		reply = GetActionDeprecatedOld("projects/retrieveproject", data);
		break;
	case GR_PROJECT_USER_ROLE:
		reply = GetActionDeprecatedOld("roles/getrolebyprojectanduser", data);
		break;
	case GR_CUSTOMER_ACCESSES:
		reply = GetActionDeprecatedOld("projects/getcustomeraccessbyproject", data);
		break;
	case GR_CUSTOMER_ACCESS_BY_ID:
		reply = GetActionDeprecatedOld("projects/getcustomeraccessbyid", data);
		break;
	case GR_USERPROJECT_BUG:
		reply = GetActionDeprecatedOld("bugtracker/getticketsbyuser", data);
		break;
	case GR_XLAST_BUG_OFFSET:
		reply = GetActionDeprecatedOld("bugtracker/getlasttickets", data);
		break;
	case GR_XLAST_BUG_OFFSET_BY_STATE:
		reply = GetActionDeprecatedOld("bugtracker/getticketsbystate", data);
		break;
	case GR_XLAST_BUG_OFFSET_CLOSED:
		reply = GetActionDeprecatedOld("bugtracker/getlastclosedtickets", data);
		break;
	case GR_PROJECTBUG_ALL:
		reply = GetActionDeprecatedOld("bugtracker/gettickets", data);
		break;
	case GR_BUGCOMMENT:
		reply = GetActionDeprecatedOld("bugtracker/getcomments", data);
		break;
	case GR_GETBUGS_STATUS:
		reply = GetActionDeprecatedOld("bugtracker/getStates", data);
		break;
	case GR_LIST_TIMELINE:
		reply = GetActionDeprecatedOld("timeline/gettimelines", data);
		break;
	case GR_TIMELINE:
		reply = GetActionDeprecatedOld("timeline/getlastmessages", data);
		break;
	case GR_COMMENT_TIMELINE:
		reply = GetActionDeprecatedOld("timeline/getcomments", data);
		break;
	case GR_USER_DATA:
		reply = GetActionDeprecatedOld("user/getuserbasicinformations", data);
		break;
    case GR_PROJECTBUGTAG_ALL:
		reply = GetActionDeprecatedOld("bugtracker/getprojecttags", data);
		break;
	case GR_PROJECT_USERS_ALL:
		reply = GetActionDeprecated("projects/getusertoproject", data);
		break;
/*	case GR_BUG:
		reply = GetActionDeprecatedOld("bugtracker/getticket", data);
        break;*/
	case GR_CALENDAR:
		reply = GetActionDeprecated("planning/getmonth", data);
		break;
	case GR_EVENT:
		reply = GetActionDeprecated("event/getevent", data);
		break;
	case GR_TYPE_EVENT:
		reply = GetActionDeprecated("event/gettypes", data);
		break;
	case GR_WHITEBOARD:
		reply = GetActionDeprecated("whiteboard/list", data);
		break;
	}
	if (reply == nullptr)
        throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int DataConnectorOnline::Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case DR_PROJECT_ROLE:
		reply = DeleteProjectRole(data);
		break;
	case DR_ROLE_DETACH:
		reply = DetachRole(data);
		break;
	case DR_PROJECT_USER:
		reply = DeleteProjectUser(data);
		break;
	case DR_PROJECT:
		reply = DeleteProject(data);
		break;
	case DR_CUSTOMER_ACCESS:
		reply = DeleteCustomerAccess(data);
		break;
	case DR_CLOSE_TICKET_OR_COMMENT:
		reply = RESTDelete(data, "bugtracker/closeticket");
		break;
	case DR_REMOVE_BUGTAG:
		reply = RESTDelete(data, "bugtracker/removetag");
		break;
	case DR_REMOVE_EVENT:
		reply = DeleteActionDeprecated("event/delevent", data);
		break;
	}
	if (reply == nullptr)
        throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int API::DataConnectorOnline::Put(DataPart part, int request, QVector<QString>& data, QObject * requestResponseObject, const char * slotSuccess, const char * slotFailure)
{
	QNetworkReply *reply = nullptr;
	switch (request)
	{
	case PUTR_USERSETTINGS:
		reply = PutUserSettings(data);
		break;
	case PUTR_PROJECTSETTINGS:
		reply = PutProjectSettings(data);
		break;
	case PUTR_INVITE_USER:
		reply = ProjectInvite(data);
		break;
	case PUTR_ASSIGNTAG:
		reply = AssignTagToBug(data);
		break;
	case PUTR_EDIT_EVENT:
		reply = EditEvent(data);
		break;
	case PUTR_SET_PARTICIPANT:
		reply = EditEventParticipant(data);
		break;
	case PUTR_ASSIGNUSER_BUG:
		reply = AssignUserToTicket(data);
		break;
	}
	if (reply == nullptr)
        throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
	return maxInt;
}

int API::DataConnectorOnline::Request(RequestType type, DataPart part, int request, QMap<QString, QVariant>& data, QObject * requestResponseObject, const char * slotSuccess, const char * slotFailure)
{
    QNetworkReply *reply = nullptr;
	switch (type)
	{
    case RT_POST:
		reply = PostAction(_PostMap[request], data);
		break;
    case RT_PUT:
		reply = PutAction(_PutMap[request], data);
		break;
    case RT_GET:
		reply = GetAction(_GetMap[request], data);
		break;
    case RT_DELETE:
		reply = DeleteAction(_DeleteMap[request], data);
		break;
	}
	if (reply == nullptr)
		throw QException();
	_CallBack[reply] = DataConnectorCallback();
	_CallBack[reply]._Request = requestResponseObject;
	_CallBack[reply]._SlotFailure = slotFailure;
	_CallBack[reply]._SlotSuccess = slotSuccess;
	int maxInt = 1;
	for (QMap<QNetworkReply*, int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
	{
		if (it.value() >= maxInt)
		{
			maxInt = it.value() + 1;
		}
	}
	_Request[reply] = maxInt;
    QJsonObject obj = ParseMap(data);
    QJsonDocument doc(obj);
    //REGISTER_REQUEST(maxInt, reply->url().toString(), doc.toJson(QJsonDocument::Indented));
	return maxInt;
}

QJsonObject DataConnectorOnline::ParseMap(QMap<QString, QVariant> &data)
{
	QJsonObject ret;
	for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
	{
        if (it.key().contains("urlfield#"))
            continue;
		if (it.value().canConvert<QString>())
			ret[it.key()] = it.value().toString();
        else if (it.value().canConvert<QList<QVariant> >())
		{
            QJsonArray arr;
            QList<QVariant> strList = it.value().toList();
            for (QVariant str : strList)
                arr.append(str.toString());
            ret[it.key()] = arr;
		}
		else
        {
            QMap<QString, QVariant> map = it.value().toMap();
            ret[it.key()] = ParseMap(map);
        }
	}
	return ret;
}

QNetworkReply * API::DataConnectorOnline::PostAction(QString urlIn, QMap<QString, QVariant>& data)
{
    QString urlAddon("");
     for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
     {
         if (it.key().contains("urlfield#"))
         {
             QVariant var = it.value();
             urlAddon += "/" + var.toString();
         }
     }
	QJsonObject json;
	QJsonObject objData = ParseMap(data);
	json["data"] = objData;

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

    QNetworkRequest requestSend(QUrl(URL_API + urlIn + urlAddon));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::PutAction(QString urlIn, QMap<QString, QVariant>& data)
{
    qDebug() << "Put action : " << urlIn;
    QString urlAddon("");
    for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
    {
        qDebug() << "Parsing url field : " << it.key();
        if (it.key().contains("urlfield#"))
        {
            qDebug() << "Url field : " << it.value();
            QVariant var = it.value();
            urlAddon += "/" + var.toString();
        }
    }
	QJsonObject json;
	QJsonObject objData = ParseMap(data);
	json["data"] = objData;

    QJsonDocument doc(json);
    LOG(doc.toJson(QJsonDocument::Indented));

    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

    QNetworkRequest requestSend(QUrl(URL_API + urlIn + urlAddon));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->put(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::DeleteAction(QString urlIn, QMap<QString, QVariant>& data)
{
	QString urlAddon = "";

	for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
	{
		QVariant var = it.value();
		urlAddon += "/" + var.toString();
	}

	QNetworkRequest requestSend(QUrl(URL_API + urlIn + urlAddon));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	QNetworkReply *request = _Manager->deleteResource(requestSend);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::GetAction(QString urlIn, QMap<QString, QVariant>& data)
{
	QString urlAddon = "";

	for (QMap<QString, QVariant>::iterator it = data.begin(); it != data.end(); ++it)
	{
		QVariant var = it.value();
		urlAddon += "/" + var.toString();
	}

	QNetworkRequest requestSend(QUrl(URL_API + urlIn + urlAddon));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	QNetworkReply *request = _Manager->get(requestSend);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::Login(QVector<QString> &data)
{
	QJsonObject json;
	json["login"] = data[0];
	json["password"] = data[1];
	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("accountadministration/login")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PutUserSettings(QVector<QString> &data)
{
	QJsonObject json;
	QJsonObject birthdayJson;
	QTimeZone timezone;

	if (data[0] != "")
		json["first_name"] = data[0];
	if (data[1] != "")
		json["last_name"] = data[1];
	if (data[2] != "")
	{
		birthdayJson["date"] = data[2];
		birthdayJson["timezone_type"] = "3";
		birthdayJson["timezone"] = timezone.displayName(QTimeZone::StandardTime);
		json["birthday"] = birthdayJson;
	}

	if (data[3] != "")
		json["avatar"] = data[3];
	if (data[4] != "")
		json["phone"] = data[4];
	if (data[5] != "")
		json["country"] = data[5];
	if (data[6] != "")
		json["linkedin"] = data[6];
	if (data[7] != "")
		json["viadeo"] = data[7];
	if (data[8] != "")
		json["twitter"] = data[8];
	if (data[9] != "")
		json["password"] = data[9];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("user/basicinformations/") + data[10]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->put(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PutProjectSettings(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	if (data[2] != "")
		json["name"] = data[2];
	if (data[3] != "")
		json["description"] = data[3];
	if (data[4] != "")
		json["logo"] = data[4];
	if (data[5] != "")
		json["phone"] = data[5];
	if (data[6] != "")
		json["company"] = data[6];
	if (data[7] != "")
		json["email"] = data[7];
	if (data[8] != "")
		json["facebook"] = data[8];
	if (data[9] != "")
		json["twitter"] = data[9];
	if (data[10] != "")
		json["password"] = data[10];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/updateinformations")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->put(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::Logout(QVector<QString> &data)
{
	QString url = OLD_URL_API + QString("accountadministration/logout");
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}

	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply * API::DataConnectorOnline::GetActionDeprecated(QString urlIn, QVector<QString>& data)
{
	QString url = URL_API + urlIn;
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}
	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply *DataConnectorOnline::GetActionDeprecatedOld(QString urlIn, QVector<QString> &data)
{
	QString url = OLD_URL_API + urlIn;
	for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
	{
		url += QString("/") + *it;
	}
	QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

	return request;
}

QNetworkReply *DataConnectorOnline::AddRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];
	json["teamTimeline"] = data[3];
	json["customerTimeline"] = data[4];
	json["gantt"] = data[5];
	json["whiteboard"] = data[6];
	json["bugtracker"] = data[7];
	json["event"] = data[8];
	json["task"] = data[9];
	json["projectSettings"] = data[10];
	json["cloud"] = data[11];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/addprojectroles")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["roleId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/delprojectroles")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;

}

QNetworkReply *DataConnectorOnline::AttachRole(QVector<QString> &data)
{
	QJsonObject json;

	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];
	json["roleId"] = data[3];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/assignpersontorole")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DetachRole(QVector<QString> &data)
{
	QJsonObject json;


	json["_token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];
	json["roleId"] = data[3];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("roles/delpersonrole")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	qDebug() << *jsonba;
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::ProjectInvite(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["userEmail"] = data[2];

	QJsonDocument doc(json);
	QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/addusertoproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
	QNetworkReply *request = _Manager->post(requestSend, jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectUser(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["userId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/removeusertoproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteCustomerAccess(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["customerAccessId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/delcustomeraccess")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteProject(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/delproject")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditBug(QVector<QString> &data)
{
	QJsonObject json;

	//data[0] = id dans l'URL
	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["stateId"] = data[4];
	json["stateName"] = data[5];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/editticket/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::OpenBug(QVector<QString> &data)
{
	QJsonObject json;
	QString idProject = data[0];

	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["stateId"] = data[4];
	json["stateName"] = data[5];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/postticket/") + idProject));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CommentBug(QVector<QString> &data)
{
	QJsonObject json;
	QString idProject = data[0];

	json["token"] = data[1];
	json["title"] = data[2];
	json["description"] = data[3];
	json["parentId"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/postcomment/") + idProject));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CustomerGenerateAccess(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("projects/generatecustomeraccess")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditMessageTimeline(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[1];
	json["messageId"] = data[2];
	json["title"] = data[3];
	json["message"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("timeline/editmessage/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::PostMessageTimeline(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[1];
	json["title"] = data[2];
	json["message"] = data[3];
	if (data.size() > 4)
		json["commentedId"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("timeline/postmessage/") + data[0]));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::PostEvent(QVector<QString>& data)
{
	QJsonObject json;

	QJsonObject dataJson;
	dataJson["token"] = data[0];
	dataJson["projectId"] = data[1];
	dataJson["title"] = data[2];
	dataJson["description"] = data[3];
	dataJson["icon"] = data[4];
	dataJson["typeId"] = data[5];
	dataJson["begin"] = data[6];
	dataJson["end"] = data[7];

	json["data"] = dataJson;

	qDebug() << "Data : " << json;

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("event/postevent")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::PostNewWhiteboard(QVector<QString>& data)
{
	QJsonObject json;

	QJsonObject dataJson;
	dataJson["token"] = data[0];
	dataJson["projectId"] = data[1];
	dataJson["whiteboardName"] = data[2];

	json["data"] = dataJson;

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("whiteboard/new")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *API::DataConnectorOnline::EditEvent(QVector<QString>& data)
{
	QJsonObject json;

	QJsonObject objData;
	objData["token"] = data[0];
	objData["eventId"] = data[1];
	objData["title"] = data[2];
	objData["description"] = data[3];
	objData["icon"] = data[4];
	objData["typeId"] = data[5];
	objData["begin"] = data[6];
	objData["end"] = data[7];

	json["data"] = objData;

	QJsonDocument doc(json);
	
	qDebug() << doc.toJson();

	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("event/editevent")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->put(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply * API::DataConnectorOnline::EditEventParticipant(QVector<QString>& data)
{
	QJsonObject json;

	QJsonObject dataObj;

	QJsonArray toAdd;
	QJsonArray toRemove;

	dataObj["token"] = data[0];
	dataObj["eventId"] = data[1];
	bool AddMod = true;
	for (size_t i = 2; i < data.size(); i++)
	{
		if (data[i] == "#")
		{
			AddMod = false;
			continue;
		}
		if (AddMod)
			toAdd.push_back(data[i]);
		else
			toRemove.push_back(data[i]);
	}
	dataObj["toAdd"] = toAdd;
	dataObj["toRemove"] = toRemove;

	json["data"] = dataObj;

	QJsonDocument doc(json);

	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(URL_API + QString("event/setparticipants")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->put(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::AssignTagToBug(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["bugId"] = data[1];
	json["tagId"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/assigntag")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->put(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::AssignUserToTicket(QVector<QString> &data)
{
	QJsonObject json;
	QJsonArray toAdd;
	QString bugId = data[0];

	for (int i = 2; i < data.length(); ++i)
		toAdd.append(data[i]);

	json["token"] = data[1];
	json["toRemove"] = QJsonArray();
	json["toAdd"] = toAdd;

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/setparticipants/") + bugId));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteUserToTicket(QVector<QString> &data)
{
	QJsonObject json;
	QJsonArray toRemove;
	QString bugId = data[0];

	for (int i = 2; i < data.length(); ++i)
		toRemove.append(data[i]);

	json["token"] = data[1];
	json["toRemove"] = toRemove;
	json["toAdd"] = QJsonArray();

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/setparticipants/") + bugId));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::CreateTag(QVector<QString> &data)
{
	QJsonObject json;

	json["token"] = data[0];
	json["projectId"] = data[1];
	json["name"] = data[2];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/tagcreation")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::EditCommentBug(QVector<QString> &data)
{
	QJsonObject json;

	json["projectId"] = data[0];
	json["token"] = data[1];
	json["commentId"] = data[2];
	json["title"] = data[3];
	json["description"] = data[4];

	QJsonDocument doc(json);
	QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
	QNetworkRequest requestSend(QUrl(OLD_URL_API + QString("bugtracker/editcomment")));
	requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
	requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
	QNetworkReply *request = _Manager->post(requestSend, *jsonba);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::RESTDelete(QVector<QString> &data, QString baseURL)
{
	QString URL = baseURL;
	QVector<QString>::iterator dataIt;

	for (dataIt = data.begin(); dataIt != data.end(); ++dataIt)
		URL += "/" + *dataIt;

	QNetworkRequest requestSend(QUrl(OLD_URL_API + URL));
	QNetworkReply *request = _Manager->deleteResource(requestSend);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}

QNetworkReply *DataConnectorOnline::DeleteActionDeprecated(QString urlIn, QVector<QString> &data)
{
	QString URL = urlIn;
	QVector<QString>::iterator dataIt;

	for (QString item : data)
		URL += "/" + item;

	QNetworkRequest requestSend(QUrl(URL_API + URL));
	QNetworkReply *request = _Manager->deleteResource(requestSend);
	QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
	return request;
}
