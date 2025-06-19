const { sendPrayaRequest } = require("../helpers/prayaRequest");

async function sendData(req, res) {
    const { payload, url, method = "POST", token = null } = req.body;

    console.log(req)
    if (!payload || !url || !method) {
        return res.status(400).json({
            status: "error",
            message: "payload, url, and method are required",
        });
    }

    // try {
        const prayaResponse = await sendPrayaRequest({
            url,
            method,
            payload,
            token,
        });

        return res.json(prayaResponse);
    // } catch (error) {
    //     return res.status(500).json({
    //         status: "error",
    //         message: "Internal server error",
    //     });
    // }
}

module.exports = { sendData };
