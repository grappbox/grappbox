#ifndef IBODYCONTENER
#define IBODYCONTENER

#include "MainWindow.h"

class IBodyContener
{
public:
    virtual ~IBodyContener() {}

    virtual void Show(int ID, MainWindow *mainApp) = 0;
    virtual void Hide() = 0;
};

#endif // IBODYCONTENER

