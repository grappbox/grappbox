#ifndef IDATACONNECTOR
#define IDATACONNECTOR

#include <QVector>
#include <QString>
#include <QObject>

namespace API
{

    enum DataPart
    {
        DP_WHITEBOARD,
        DP_PROJECT,
        DP_GANTT,
        DP_TASK,
        DP_TIMELINE,
        DP_USER_DATA,
        DP_CALENDAR
    };

    enum GetRequest
    {
        GR_CALENDAR,
        GR_CALENDAR_DAY,
        GR_LIST_GANTT,
        GR_LIST_PROJECT,
        GR_PROJECT,
        GR_CREATOR_PROJECT,
        GR_LIST_MEMBER_PROJECT,
        GR_LIST_MEETING,
        GR_LIST_TASK,
        GR_LIST_TIMELINE,
        GR_TASK,
        GR_TIMELINE,
        GR_USER_DATA,
        GR_WHITEBOARD,
        GR_LOGOUT,
        GR_USER_SETTINGS
    };

    enum PostRequest
    {
        PR_LOGIN
    };

    enum PutRequest
    {
        PUTR_UserSettings
    };

    class IDataConnector
    {

    public:

        virtual ~IDataConnector() {}

        virtual int Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Get(DataPart part, int request, QVector<QString> &data, QObject *requestReturn, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Put(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;

    };

}

#endif // IDATACONNECTOR

