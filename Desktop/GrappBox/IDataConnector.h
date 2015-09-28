#ifndef IDATACONNECTOR
#define IDATACONNECTOR

#include <QList>
#include <QString>

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
        GR_LIST_TASK,
        GR_LIST_TIMELINE,
        GR_PROJECT,
        GR_TASK,
        GR_TIMELINE,
        GR_USER_DATA,
        GR_WHITEBOARD
    };

    class IDataConnector
    {
    public:
        virtual ~IDataConnector() {};

        virtual bool Update(DataPart part, int id, QList<QString> data) = 0;
        virtual bool Get(DataPart part, int id, QList<QString> data) = 0;
        virtual bool Delete(DataPart part, int id, QList<QString> data) = 0;

    };

}

#endif // IDATACONNECTOR

