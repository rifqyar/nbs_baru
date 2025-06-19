const axios = require("axios");
const winston = require("winston");
const path = require("path");

// Setup logger (mirip Log::channel('praya'))
const prayaLogger = winston.createLogger({
    level: "info",
    format: winston.format.json(),
    transports: [
        new winston.transports.File({
            filename: path.join(__dirname, "../logs/praya.log"),
            level: "info",
        }),
    ],
});

async function sendPrayaRequest({
    url,
    method = "POST",
    payload = {},
    token = null,
}) {
    prayaLogger.info("Request to Praya (Using Axios)", {
        url,
        method,
        payload,
    });

    const headers = {
        "Content-Type": "application/json",
    };

    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    const config = {
        method,
        url,
        headers,
        data: payload,
        timeout: 0, // set_time_limit(0) equivalent
    };

    const start = Date.now();

    // try {
        const response = await axios(config);
        const duration = (Date.now() - start) / 1000;

        prayaLogger.info("Praya Response Info (Using Axios)", {
            time: duration,
            status_code: response.status,
            response: response.data,
        });

        if (response.status >= 200 && response.status < 300) {
            return {
                status: "success",
                response: response.data,
            };
        } else {
            return {
                status: "error",
                response: `HTTP Error #${response.status}: ${JSON.stringify(
                    response.data
                )}`,
                _response: response.data,
                httpCode: response.status,
            };
        }
    // } catch (error) {
    //     prayaLogger.error("Axios Error", { error: error.message });

    //     return {
    //         status: "error",
    //         response: `HTTP Error #${response.status}: ${JSON.stringify(
    //             response.data
    //         )}`,
    //         _response: response.data,
    //         httpCode: response.status,
    //     };
    // }
}

module.exports = { sendPrayaRequest };
